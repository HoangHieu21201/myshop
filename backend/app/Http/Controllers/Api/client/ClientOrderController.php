<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\UserStoreOrderRequest;
use App\Http\Requests\Order\UserUpdateOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Cart;
use App\Models\ProductVariant;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientOrderController extends Controller
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
     * Thanh toán (Checkout) - Rào trước đón sau mọi rủi ro
     */
    public function store(UserStoreOrderRequest $request)
    {
        $user = auth('sanctum')->user();
        $sessionId = $request->header('X-Cart-Session-Id');

        $cartQuery = Cart::with(['items.variant', 'items.combo']);
        $cart = $user ? $cartQuery->where('user_id', $user->id)->first() 
                      : $cartQuery->where('session_id', $sessionId)->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng của bạn đang trống'], 400);
        }

        try {
            return DB::transaction(function () use ($request, $user, $cart) {
                
                // 1. GOM TOÀN BỘ ID CỦA VARIANT (Bao gồm Sản phẩm lẻ & Món trong Combo) để Lock 1 lần
                $variantIdsToLock = [];
                foreach ($cart->items as $item) {
                    if ($item->product_variant_id) {
                        $variantIdsToLock[] = $item->product_variant_id;
                    } elseif ($item->combo_id && is_array($item->combo_selections)) {
                        $variantIdsToLock = array_merge($variantIdsToLock, array_column($item->combo_selections, 'selected_variant_id'));
                    }
                }
                
                $variants = ProductVariant::whereIn('id', array_unique($variantIdsToLock))
                            ->orderBy('id')
                            ->lockForUpdate() // Khóa dòng chống Race Condition
                            ->get()
                            ->keyBy('id');

                $subTotal = 0;
                $orderItemsData = [];

                // 2. KIỂM TRA TỒN KHO & CHUẨN BỊ DATA
                foreach ($cart->items as $item) {
                    // Xử lý Sản phẩm lẻ
                    if ($item->product_variant_id) {
                        $variant = $variants->get($item->product_variant_id);
                        if (!$variant || $variant->stock_quantity < $item->quantity) {
                            throw new \Exception("Sản phẩm SKU {$variant->sku} không đủ số lượng trong kho.");
                        }
                        
                        $variant->stock_quantity -= $item->quantity; // Trừ kho ảo trong biến nhớ
                        $variant->save();

                        $itemTotal = $item->subtotal; // Lấy luôn giá trị tạm tính từ CartItem Model
                        $subTotal += $itemTotal;

                        $orderItemsData[] = [
                            'product_id'         => $variant->product_id,
                            'product_variant_id' => $variant->id,
                            'product_name'       => $variant->product->name ?? 'Sản phẩm SORA',
                            'variant_sku'        => $variant->sku,
                            'variant_attributes' => $variant->attributes, // Tự encode vì model cast array
                            'variant_image'      => $variant->image_url,
                            'price'              => $item->price,
                            'quantity'           => $item->quantity,
                            'total_price'        => $itemTotal,
                            'combo_id'           => null,
                            'combo_selections'   => null,
                        ];
                    } 
                    // Xử lý Combo
                    elseif ($item->combo_id && $item->combo) {
                        if (is_array($item->combo_selections)) {
                            foreach ($item->combo_selections as $selection) {
                                $vId = $selection['selected_variant_id'];
                                $variant = $variants->get($vId);
                                
                                if (!$variant || $variant->stock_quantity < $item->quantity) {
                                    throw new \Exception("Một sản phẩm trong bộ {$item->combo->name} đã hết hàng.");
                                }
                                $variant->stock_quantity -= $item->quantity;
                                $variant->save();
                            }
                        }

                        $itemTotal = $item->subtotal;
                        $subTotal += $itemTotal;

                        $orderItemsData[] = [
                            'product_id'         => null,
                            'product_variant_id' => null,
                            'product_name'       => $item->combo->name,
                            'variant_sku'        => 'COMBO-' . $item->combo_id,
                            'variant_attributes' => null,
                            'variant_image'      => $item->combo->thumbnail_image,
                            'price'              => $item->price,
                            'quantity'           => $item->quantity,
                            'total_price'        => $itemTotal,
                            'combo_id'           => $item->combo_id,
                            'combo_selections'   => $item->combo_selections,
                        ];
                    }
                }

                // 3. XỬ LÝ COUPON (Bắt chặt điều kiện)
                $discountAmount = 0;
                $couponId = null;
                if ($request->coupon_code) {
                    $coupon = Coupon::where('code', $request->coupon_code)->lockForUpdate()->first();
                    
                    if (!$coupon || $coupon->status !== 'active') {
                        throw new \Exception("Mã giảm giá không hợp lệ hoặc đã hết hạn.");
                    }
                    if ($coupon->usage_limit !== null && $coupon->usage_count >= $coupon->usage_limit) {
                        throw new \Exception("Mã giảm giá đã hết lượt sử dụng.");
                    }
                    if ($subTotal < $coupon->min_spend) {
                        throw new \Exception("Chưa đạt giá trị đơn hàng tối thiểu để dùng mã này.");
                    }

                    $discountAmount = ($coupon->type === 'fixed') ? $coupon->value : ($subTotal * ($coupon->value / 100));
                    $couponId = $coupon->id;
                    $coupon->increment('usage_count');
                }

                $shippingFee = $subTotal > 500000 ? 0 : 30000;
                $totalAmount = max($subTotal - $discountAmount + $shippingFee, 0);

                // 4. TẠO ĐƠN HÀNG
                $order = Order::create([
                    'order_code'       => 'ORD-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5)),
                    'user_id'          => $user->id ?? null,
                    'customer_name'    => $request->customer_name,
                    'customer_phone'   => $request->customer_phone,
                    'customer_email'   => $request->customer_email, // Đã thêm Email
                    'customer_address' => $request->customer_address,
                    'order_note'       => $request->order_note,
                    'sub_total'        => $subTotal,
                    'discount_amount'  => $discountAmount,
                    'shipping_fee'     => $shippingFee,
                    'total_amount'     => $totalAmount,
                    'coupon_id'        => $couponId,
                    'coupon_code'      => $request->coupon_code,
                    'payment_method'   => $request->payment_method,
                    'payment_status'   => 'unpaid',
                    'status'           => 'pending',
                ]);

                // 5. LƯU CHI TIẾT
                foreach ($orderItemsData as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }

                // 6. GHI LỊCH SỬ (Chuẩn hóa changed_by_type)
                OrderStatusHistory::create([
                    'order_id'        => $order->id,
                    'new_status'      => 'pending',
                    'note'            => 'Khách hàng đặt đơn thành công',
                    'changed_by'      => $user->id ?? null,
                    'changed_by_type' => $user ? 'user' : 'guest', // Phân loại ai là người tạo
                ]);

                // 7. XÓA GIỎ HÀNG
                $cart->items()->delete();
                $cart->delete();

                return response()->json([
                    'success' => true,
                    'message' => 'Đặt hàng thành công!',
                    'data' => [
                        'order_code'   => $order->order_code,
                        'total_amount' => $order->total_amount
                    ]
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Xem chi tiết đơn hàng (Dành cho User đã login)
     */
    public function show(string $order_code)
    {
        $user = auth('sanctum')->user();
        
        $order = Order::with(['items', 'histories'])->where('order_code', $order_code)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        // Bảo mật: Nếu có User_id, phải check xem đúng chính chủ không
        if ($order->user_id && (!$user || $user->id !== $order->user_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền xem đơn hàng này'], 403);
        }

        return response()->json(['success' => true, 'data' => $order]);
    }

    /**
     * Khách hàng chủ động HỦY đơn hàng.
     */
    public function update(UserUpdateOrderRequest $request, string $order_code)
    {
        $user = auth('sanctum')->user();
        $order = Order::with('items')->where('order_code', $order_code)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Không tìm thấy đơn hàng'], 404);
        }

        if ($order->status !== 'pending') {
            return response()->json(['success' => false, 'message' => 'Chỉ có thể hủy đơn khi đang ở trạng thái Chờ xác nhận'], 400);
        }

        if ($order->user_id && (!$user || $user->id !== $order->user_id)) {
            return response()->json(['success' => false, 'message' => 'Bạn không có quyền hủy đơn hàng này'], 403);
        }

        try {
            return DB::transaction(function () use ($order, $request, $user) {
                $order->update(['status' => 'cancelled']);

                // FIX LỖI TỬ HUYỆT: Hoàn lại tồn kho cho cả Sản phẩm lẻ VÀ Combo
                foreach ($order->items as $item) {
                    if ($item->product_variant_id) {
                        ProductVariant::where('id', $item->product_variant_id)->increment('stock_quantity', $item->quantity);
                    } elseif ($item->combo_id && is_array($item->combo_selections)) {
                        foreach ($item->combo_selections as $selection) {
                            $vId = $selection['selected_variant_id'] ?? null;
                            if ($vId) {
                                ProductVariant::where('id', $vId)->increment('stock_quantity', $item->quantity);
                            }
                        }
                    }
                }

                // Ghi lịch sử rõ ràng
                OrderStatusHistory::create([
                    'order_id'        => $order->id,
                    'old_status'      => 'pending',
                    'new_status'      => 'cancelled',
                    'note'            => 'Khách hủy: ' . $request->cancel_reason,
                    'changed_by'      => $user->id ?? null,
                    'changed_by_type' => $user ? 'user' : 'guest',
                ]);

                return response()->json(['success' => true, 'message' => 'Đã hủy đơn hàng thành công']);
            });
        } catch (\Exception $e) {
             return response()->json(['success' => false, 'message' => 'Không thể hủy đơn: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(string $id) 
    {
        return response()->json(['success' => false, 'message' => 'Xóa đơn hàng vĩnh viễn không được phép'], 403);
    }
}