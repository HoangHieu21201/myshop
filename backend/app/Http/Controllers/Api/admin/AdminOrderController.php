<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Models\Combo;
use App\Http\Requests\AdminUpdateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderRefundDealMail;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $baseQuery = Order::query();

        // Lọc ngày tháng
        if ($request->filled('start_date')) {
            $baseQuery->where('created_at', '>=', $request->start_date . ' 00:00:00');
        }
        if ($request->filled('end_date')) {
            $baseQuery->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // Lọc thanh toán chung
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $baseQuery->where('payment_status', $request->payment_status);
        }

        // Lọc tìm kiếm chung
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $baseQuery->where(function ($q) use ($searchTerm) {
                $q->where('order_code', 'like', "%{$searchTerm}%")
                    ->orWhere('customer_name', 'like', "%{$searchTerm}%")
                    ->orWhere('customer_phone', 'like', "%{$searchTerm}%");
            });
        }

        // =========================================================================
        // PHÂN LUỒNG 1: DÀNH RIÊNG CHO TRANG XỬ LÝ HOÀN TRẢ
        // =========================================================================
        if ($request->boolean('is_return_page')) {
            // Chỉ lấy các đơn Hàng bị trả lại, YÊU CẦU HOÀN TRẢ, HOẶC bị Hủy nhưng khách đã thanh toán
            $baseQuery->where(function ($q) {
                $q->whereIn('status', ['returned', 'return_requested']) // <-- ĐÃ BỔ SUNG TRẠNG THÁI MỚI VÀO ĐÂY
                    ->orWhere(function ($sub) {
                        $sub->where('status', 'cancelled')->whereIn('payment_status', ['paid', 'refunded']);
                    });
            });

            // Tối ưu hóa: Lấy một bản sao để tính toán toàn bộ các Tab chỉ bằng 1 lần gom nhóm
            $countQuery = clone $baseQuery;
            $allReturns = $countQuery->get(['payment_status', 'refund_amount']);

            $counts = [
                'all'       => $allReturns->count(),
                'pending'   => $allReturns->where('payment_status', 'paid')->whereNull('refund_amount')->count(),
                'proposing' => $allReturns->filter(fn($q) => $q->payment_status === 'paid' && $q->refund_amount !== null && (float)$q->refund_amount > 0)->count(),
                'refunded'  => $allReturns->where('payment_status', 'refunded')->count(),
                'rejected'  => $allReturns->filter(fn($q) => $q->payment_status === 'paid' && $q->refund_amount !== null && (float)$q->refund_amount === 0.0)->count(),
            ];

            // Áp dụng bộ lọc Tab do Frontend gửi lên
            if ($request->filled('return_tab') && $request->return_tab !== 'all') {
                $tab = $request->return_tab;
                if ($tab === 'pending') {
                    $baseQuery->where('payment_status', 'paid')->whereNull('refund_amount');
                } elseif ($tab === 'proposing') {
                    $baseQuery->where('payment_status', 'paid')->whereNotNull('refund_amount')->where('refund_amount', '>', 0);
                } elseif ($tab === 'refunded') {
                    $baseQuery->where('payment_status', 'refunded');
                } elseif ($tab === 'rejected') {
                    $baseQuery->where('payment_status', 'paid')->whereNotNull('refund_amount')->where('refund_amount', 0);
                }
            }
        }
        // PHÂN LUỒNG 2: DÀNH CHO TRANG QUẢN LÝ ĐƠN HÀNG BÌNH THƯỜNG
        else {
            $countQuery = clone $baseQuery;
            $rawCounts = $countQuery->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $counts = [
                'all'        => array_sum($rawCounts) - ($rawCounts['returned'] ?? 0) - ($rawCounts['return_requested'] ?? 0),
                'pending'    => $rawCounts['pending'] ?? 0,
                'confirmed'  => $rawCounts['confirmed'] ?? 0,
                'processing' => $rawCounts['processing'] ?? 0,
                'shipping'   => $rawCounts['shipping'] ?? 0,
                'delivered'  => $rawCounts['delivered'] ?? 0,
                'cancelled'  => $rawCounts['cancelled'] ?? 0,
                'returned'   => $rawCounts['returned'] ?? 0,
            ];

            // ĐÂY LÀ CHỖ CHẶN LẠI NÈ SẾP
            if ($request->filled('status') && $request->status !== 'all') {
                $baseQuery->where('status', $request->status);
            } else {
                // Khi ở Tab "Tất cả", loại trừ hẳn 2 trạng thái thuộc về phần Hoàn Trả
                $baseQuery->whereNotIn('status', ['returned', 'return_requested']);
            }
        }

        $orders = $baseQuery->with(['user:id,fullName,email'])
            ->withCount('items')
            ->orderBy('id', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data'    => $orders,
            'counts'  => $counts
        ]);
    }

    public function show($id)
    {
        $order = Order::with([
            'user:id,fullName,email,phone',
            'items.product:id,slug',
            'histories.changer:id,fullName'
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $order]);
    }

    public function updateStatus(AdminUpdateOrderRequest $request, $id)
    {
        DB::beginTransaction();
        try {
            $order = Order::with('items')->findOrFail($id);
            $oldStatus = $order->status;
            $newStatus = $request->status;
            $newPaymentStatus = $request->payment_status;
            $hasChanged = false;

            if ($oldStatus !== $newStatus) {
                $order->status = $newStatus;
                $hasChanged = true;

                OrderStatusHistory::create([
                    'order_id'        => $order->id,
                    'old_status'      => $oldStatus,
                    'new_status'      => $newStatus,
                    'note'            => $request->note,
                    'changed_by'      => Auth::id(),
                    'changed_by_type' => 'admin'
                ]);

                if (in_array($newStatus, ['cancelled', 'returned'])) {
                    foreach ($order->items as $item) {
                        if ($item->product_variant_id) {
                            ProductVariant::where('id', $item->product_variant_id)->increment('stock_quantity', $item->quantity);
                        } elseif ($item->combo_id) {
                            Combo::where('id', $item->combo_id)->whereNotNull('usage_limit')->increment('usage_limit', $item->quantity);

                            if (is_array($item->combo_selections)) {
                                foreach ($item->combo_selections as $selection) {
                                    $vId = $selection['selected_variant_id'] ?? null;
                                    if ($vId) {
                                        ProductVariant::where('id', $vId)->increment('stock_quantity', $item->quantity);
                                    }
                                }
                            }

                            $combo = Combo::with('items')->find($item->combo_id);
                            if ($combo) {
                                foreach ($combo->items as $cItem) {
                                    if ($cItem->product_variant_id) {
                                        $totalQtyToRestore = $item->quantity * $cItem->quantity;
                                        ProductVariant::where('id', $cItem->product_variant_id)->increment('stock_quantity', $totalQtyToRestore);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if ($order->payment_status !== $newPaymentStatus) {
                $order->payment_status = $newPaymentStatus;
                $hasChanged = true;
            }

            if ($hasChanged) {
                $order->save();
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cập nhật trạng thái đơn hàng thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()], 500);
        }
    }

    public function processRefundAction(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:propose,reject,refunded',
            'refund_amount' => 'required|numeric|min:0',
            'refund_note' => 'nullable|string'
        ]);

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);

            // Ép refund_amount về 0 nếu TỪ CHỐI, làm cờ (flag) nhận biết trạng thái
            $order->refund_amount = $request->action === 'reject' ? 0 : $request->refund_amount;
            $order->refund_note = $request->refund_note;

            if ($request->action === 'refunded') {
                $order->payment_status = 'refunded';
                if ($order->status !== 'returned') {
                    $order->status = 'returned'; // Chốt chặn an toàn
                }

                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'old_status' => $order->status,
                    'new_status' => $order->status,
                    'note' => 'Kế toán xác nhận Đã chuyển khoản hoàn tiền.',
                    'changed_by' => Auth::id(),
                    'changed_by_type' => 'admin'
                ]);
            } else {
                if ($order->customer_email) {
                    try {
                        Mail::to($order->customer_email)->send(new OrderRefundDealMail($order, $request->action));
                    } catch (\Exception $e) {
                        // Bỏ qua lỗi kết nối Mail cục bộ
                    }
                }

                $historyNote = $request->action === 'propose' ? 'Đã gửi Email thỏa thuận số tiền hoàn lại.' : 'Đã gửi Email từ chối hoàn tiền.';
                OrderStatusHistory::create([
                    'order_id' => $order->id,
                    'old_status' => $order->status,
                    'new_status' => $order->status,
                    'note' => $historyNote,
                    'changed_by' => Auth::id(),
                    'changed_by_type' => 'admin'
                ]);
            }

            $order->save();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Xử lý thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        if (!in_array($order->status, ['cancelled', 'returned'])) {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể xóa hóa đơn đã Hủy hoặc Hoàn trả'], 400);
        }
        $order->delete();
        return response()->json(['success' => true, 'message' => 'Đã đưa đơn hàng vào thùng rác']);
    }
}
