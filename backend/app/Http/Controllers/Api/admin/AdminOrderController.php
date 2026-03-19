<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Http\Requests\UpdateOrderRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; 

class AdminOrderController extends Controller
{
    // 1. LẤY DANH SÁCH ĐƠN HÀNG (KÈM BỘ ĐẾM SIÊU TỐC)
    public function index(Request $request)
    {
        $query = Order::with(['user:id,fullName,email'])
                      ->withCount('items');

        // Lọc theo trạng thái nếu có
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        
        // Lọc theo trạng thái thanh toán
        if ($request->has('payment_status') && $request->payment_status !== 'all') {
            $query->where('payment_status', $request->payment_status);
        }

        // Lọc theo khoảng ngày (Bổ sung bộ lọc ngày)
        if ($request->has('start_date') && $request->start_date != '') {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }

        $orders = $query->orderBy('id', 'desc')->paginate(15);

        // THUẬT TOÁN ĐẾM NHANH (Chỉ tốn 1 Query cho tất cả các Tab)
        $rawCounts = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $counts = [
            'all' => array_sum($rawCounts),
            'pending' => $rawCounts['pending'] ?? 0,
            'confirmed' => $rawCounts['confirmed'] ?? 0,
            'processing' => $rawCounts['processing'] ?? 0,
            'shipping' => $rawCounts['shipping'] ?? 0,
            'delivered' => $rawCounts['delivered'] ?? 0,
            'cancelled' => $rawCounts['cancelled'] ?? 0,
            'returned' => $rawCounts['returned'] ?? 0,
        ];

        return response()->json([
            'success' => true, 
            'data' => $orders,
            'counts' => $counts 
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

    public function updateStatus(UpdateOrderRequest $request, $id)
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
                    'order_id' => $order->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'note' => $request->note,
                    'changed_by' => Auth::id() 
                ]);

                // Hoàn trả Tồn kho nếu Hủy đơn
                if ($newStatus === 'cancelled') {
                    foreach ($order->items as $item) {
                        if ($item->product_variant_id) {
                            ProductVariant::where('id', $item->product_variant_id)
                                ->increment('stock_quantity', $item->quantity);
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
        if ($order->status !== 'cancelled' && $order->status !== 'returned') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể xóa hóa đơn đã Hủy hoặc Hoàn trả'], 400);
        }
        $order->delete();
        return response()->json(['success' => true, 'message' => 'Đã đưa đơn hàng vào thùng rác']);
    }
}