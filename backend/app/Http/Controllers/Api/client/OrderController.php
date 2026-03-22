<?php

namespace App\Http\Controllers\Api\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Lấy danh sách đơn hàng của User hiện tại.
     */
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng đăng nhập để xem lịch sử đơn hàng',
                'data' => []
            ], 401);
        }

        $orders = Order::with('items')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    /**
     * Tạo đơn hàng mới (Checkout).
     * Đã tách validation sang StoreOrderRequest.
     */
    public function store(StoreOrderRequest $request)
    {
        $user = auth('sanctum')->user();
        $sessionId = $request->header('X-Cart-Session-Id');

        // 1. Tìm giỏ hàng
        $cartQuery = Cart::with('items.variant');
        $cart = $user ? $cartQuery->where('user_id', $user->id)->first() 
                      : $cartQuery->where('session_id', $sessionId)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng của bạn đang trống'], 400);
        }

        return DB::transaction(function () use ($request, $user, $cart) {
            // 2. KHÓA DÒNG ĐỂ CHỐNG ÂM KHO
            $variantIds = $cart->items->pluck('product_variant_id')->toArray();
            $variants = ProductVariant::whereIn('id', $variantIds)
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->get()
                            ->keyBy('id');

            $subTotal = 0;
            $orderItemsData = [];

            // 3. Kiểm tra tồn kho & Chuẩn bị dữ liệu
            foreach ($cart->items as $item) {
                $variant = $variants->get($item->product_variant_id);

                if (!$variant || $variant->stock_quantity < $item->quantity) {
                    throw new \Exception("Sản phẩm SKU {$variant->sku} không đủ số lượng trong kho.");
                }

                $itemTotal = $variant->price * $item->quantity;
                $subTotal += $itemTotal;

                $orderItemsData[] = [
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $item->product_name ?? 'Sản phẩm trang sức',
                    'variant_sku' => $variant->sku,
                    'variant_attributes' => json_encode($item->variant_attributes ?? []),
                    'variant_image' => $variant->image_url,
                    'price' => $variant->price,
                    'quantity' => $item->quantity,
                    'total_price' => $itemTotal,
                ];
            }

            // 4. Xử lý Mã giảm giá (Coupon)
            $discountAmount = 0;
            $couponId = null;
            if ($request->coupon_code) {
                $coupon = Coupon::where('code', $request->coupon_code)->lockForUpdate()->first();
                if ($coupon && $subTotal >= $coupon->min_spend && $coupon->status === 'active') {
                    $discountAmount = ($coupon->type === 'fixed') ? $coupon->value : ($subTotal * ($coupon->value / 100));
                    $couponId = $coupon->id;
                    $coupon->increment('usage_count');
                }
            }

            $shippingFee = $subTotal > 500000 ? 0 : 30000;
            $totalAmount = max($subTotal - $discountAmount + $shippingFee, 0);

            // 5. Tạo Order
            $order = Order::create([
                'order_code' => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                'user_id' => $user->id ?? null,
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'order_note' => $request->order_note,
                'sub_total' => $subTotal,
                'discount_amount' => $discountAmount,
                'shipping_fee' => $shippingFee,
                'total_amount' => $totalAmount,
                'coupon_id' => $couponId,
                'coupon_code' => $request->coupon_code,
                'payment_method' => $request->payment_method,
                'payment_status' => 'unpaid',
                'status' => 'pending',
            ]);

            // 6. Lưu Chi tiết & Trừ Tồn Kho
            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderItem::create($itemData);
                ProductVariant::where('id', $itemData['product_variant_id'])->decrement('stock_quantity', $itemData['quantity']);
            }

            // 7. Ghi lại lịch sử
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'new_status' => 'pending',
                'note' => 'Hệ thống: Khách hàng đặt đơn thành công',
                'changed_by' => $user->id ?? null,
            ]);

            // 8. Xóa giỏ hàng
            $cart->delete();

            return response()->json([
                'success' => true,
                'message' => 'Đặt hàng thành công!',
                'data' => [
                    'order_code' => $order->order_code,
                    'total_amount' => $order->total_amount
                ]
            ]);
        });
    }

    /**
     * Xem chi tiết đơn hàng theo mã.
     */
    public function show(string $order_code)
    {
        $user = auth('sanctum')->user();
        $order = Order::with(['items', 'histories'])->where('order_code', $order_code)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ($order->user_id && (!$user || $user->id !== $order->user_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xem đơn hàng này'], 403);
        }

        return response()->json(['success' => true, 'data' => $order]);
    }

    /**
     * Khách hàng chủ động HỦY đơn hàng.
     * Đã tách validation sang UpdateOrderRequest.
     */
    public function update(UpdateOrderRequest $request, string $order_code)
    {
        $user = auth('sanctum')->user();
        $order = Order::where('order_code', $order_code)->firstOrFail();

        if ($order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể hủy đơn khi đang chờ xác nhận'], 400);
        }

        if ($order->user_id && (!$user || $user->id !== $order->user_id)) {
            return response()->json(['success' => false, 'message' => 'Lỗi xác nhận quyền sở hữu'], 403);
        }

        return DB::transaction(function () use ($order, $request, $user) {
            $order->update(['status' => 'cancelled']);

            // Hoàn lại tồn kho
            foreach ($order->items as $item) {
                ProductVariant::where('id', $item->product_variant_id)->increment('stock_quantity', $item->quantity);
            }

            // Ghi lịch sử
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'old_status' => 'pending',
                'new_status' => 'cancelled',
                'note' => 'Khách hủy: ' . $request->cancel_reason,
                'changed_by' => $user->id ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'Đã hủy đơn hàng thành công']);
        });
    }

    public function create() {}
    public function edit(string $id) {}
    public function destroy(string $id) 
    {
        return response()->json(['success' => false, 'message' => 'Xóa đơn hàng vĩnh viễn không được phép'], 403);
    }
}