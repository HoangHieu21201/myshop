<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Http\Requests\AdminUpdateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AdminOrderController extends Controller
{
    // 1. LẤY DANH SÁCH ĐƠN HÀNG (ĐÃ FIX LỖI BỘ ĐẾM LÁO)
    public function index(Request $request)
    {
        $baseQuery = Order::query();

        // Lọc theo khoảng ngày (Áp dụng chung cho cả List và Count)
        if ($request->filled('start_date')) {
            $baseQuery->where('created_at', '>=', $request->start_date . ' 00:00:00');
        }
        if ($request->filled('end_date')) {
            $baseQuery->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        // Lọc theo trạng thái thanh toán
        if ($request->filled('payment_status') && $request->payment_status !== 'all') {
            $baseQuery->where('payment_status', $request->payment_status);
        }

        // BẢN SAO QUERY ĐỂ ĐẾM CHÍNH XÁC (Clone để không dính filter status bên dưới)
        $countQuery = clone $baseQuery;
        $rawCounts = $countQuery->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $counts = [
            'all'        => array_sum($rawCounts),
            'pending'    => $rawCounts['pending'] ?? 0,
            'confirmed'  => $rawCounts['confirmed'] ?? 0,
            'processing' => $rawCounts['processing'] ?? 0,
            'shipping'   => $rawCounts['shipping'] ?? 0,
            'delivered'  => $rawCounts['delivered'] ?? 0,
            'cancelled'  => $rawCounts['cancelled'] ?? 0,
            'returned'   => $rawCounts['returned'] ?? 0,
        ];

        // Lọc theo trạng thái cho danh sách hiển thị
        if ($request->filled('status') && $request->status !== 'all') {
            $baseQuery->where('status', $request->status);
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

    // 2. XEM CHI TIẾT ĐƠN HÀNG
    public function show($id)
    {
        $order = Order::with([
            'user:id,fullName,email,phone',
            'items.product:id,slug',
            'histories.changer:id,fullName'
        ])->findOrFail($id);

        return response()->json(['success' => true, 'data' => $order]);
    }

    // 3. CẬP NHẬT TRẠNG THÁI (ĐÃ VÁ LỖ HỔNG COMBO & LOGIC)
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

                // Ghi nhận lịch sử (Đã thêm changed_by_type)
                OrderStatusHistory::create([
                    'order_id'        => $order->id,
                    'old_status'      => $oldStatus,
                    'new_status'      => $newStatus,
                    'note'            => $request->note,
                    'changed_by'      => Auth::id(),
                    'changed_by_type' => 'admin' // Định danh rõ Admin là người sửa
                ]);

                // FIX BLIND SPOT: Hoàn trả Tồn kho khi Hủy đơn HOẶC Trả hàng (Bao gồm cả Combo)
                if (in_array($newStatus, ['cancelled', 'returned'])) {
                    foreach ($order->items as $item) {
                        if ($item->product_variant_id) {
                            ProductVariant::where('id', $item->product_variant_id)
                                ->increment('stock_quantity', $item->quantity);
                        }
                        // Hoàn kho cho từng món trong Combo
                        elseif ($item->combo_id && is_array($item->combo_selections)) {
                            foreach ($item->combo_selections as $selection) {
                                $vId = $selection['selected_variant_id'] ?? null;
                                if ($vId) {
                                    ProductVariant::where('id', $vId)->increment('stock_quantity', $item->quantity);
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

    // 4. XÓA MỀM
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
