<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Models\UserAddress;
use App\Models\Coupon;
use App\Http\Requests\UserCheckoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail; // Nạp thư viện Mail
use App\Mail\OrderPlacedMail;        // Nạp Class Mail vừa tạo
use Illuminate\Support\Facades\Log;  // Nạp thư viện ghi log lỗi

class ClientCheckoutController extends Controller
{
    public function initData(Request $request)
    {
        $cart = $this->resolveCart($request);
        $cartItems = $cart ? $cart->items->load(['variant.product', 'combo']) : [];

        $addresses = [];
        $userData = null;

        $user = auth('sanctum')->user();
        if ($user) {
            $addresses = UserAddress::where('user_id', $user->id)->get();
            $userData = [
                'id'    => $user->id,
                'name'  => $user->fullName ?? $user->name ?? '',
                'email' => $user->email ?? '',
                'phone' => $user->phone ?? ''
            ];
        }

        $coupons = Coupon::where('status', 'active')
            ->where(function($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function($q) {
                $q->whereNull('usage_limit')->orWhereColumn('usage_count', '<', 'usage_limit');
            })
            ->get();

        return response()->json([
            'success'    => true,
            'cart_items' => $cartItems,
            'addresses'  => $addresses,
            'coupons'    => $coupons,
            'user'       => $userData
        ]);
    }

    public function processCheckout(UserCheckoutRequest $request)
    {
        $cart = $this->resolveCart($request);

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Giỏ hàng trống hoặc phiên đã hết hạn.'], 400);
        }

        $user = auth('sanctum')->user();

        try {
            return DB::transaction(function () use ($request, $cart, $user) {
                
                $customerName = $request->customer_name;
                $customerPhone = $request->customer_phone;
                $customerAddress = $request->customer_address;

                if ($request->user_address_id && $user) {
                    $address = UserAddress::where('user_id', $user->id)->find($request->user_address_id);
                    if ($address) {
                        $customerName = $address->customer_name;
                        $customerPhone = $address->customer_phone;
                        $customerAddress = $address->shipping_address . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->city;
                    }
                }

                $variantIdsToLock = [];
                foreach ($cart->items as $item) {
                    if ($item->product_variant_id) {
                        $variantIdsToLock[] = $item->product_variant_id;
                    } elseif ($item->combo_id && is_array($item->combo_selections)) {
                        $variantIdsToLock = array_merge($variantIdsToLock, array_column($item->combo_selections, 'selected_variant_id'));
                    }
                }

                $variants = ProductVariant::whereIn('id', array_unique($variantIdsToLock))
                    ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                $subTotal = 0;
                $orderItemsData = [];

                foreach ($cart->items as $item) {
                    if ($item->product_variant_id) {
                        $variant = $variants->get($item->product_variant_id);
                        if (!$variant || $variant->stock_quantity < $item->quantity) {
                            throw new \Exception("Sản phẩm SKU {$variant->sku} không đủ số lượng.");
                        }

                        $variant->stock_quantity -= $item->quantity;
                        $variant->save();

                        $itemTotal = $item->subtotal;
                        $subTotal += $itemTotal;

                        $orderItemsData[] = [
                            'product_id'         => $variant->product_id,
                            'product_variant_id' => $variant->id,
                            'product_name'       => $variant->product->name ?? 'Sản phẩm SORA',
                            'variant_sku'        => $variant->sku,
                            'variant_attributes' => $variant->attributes,
                            'variant_image'      => $variant->image_url,
                            'price'              => $item->price,
                            'quantity'           => $item->quantity,
                            'total_price'        => $itemTotal,
                            'combo_id'           => null,
                            'combo_selections'   => null,
                        ];
                    } elseif ($item->combo_id && $item->combo) {
                        if (is_array($item->combo_selections)) {
                            foreach ($item->combo_selections as $selection) {
                                $vId = $selection['selected_variant_id'] ?? null;
                                if ($vId) {
                                    $variant = $variants->get($vId);
                                    if (!$variant || $variant->stock_quantity < $item->quantity) {
                                        throw new \Exception("Một sản phẩm trong bộ {$item->combo->name} đã hết hàng.");
                                    }
                                    $variant->stock_quantity -= $item->quantity;
                                    $variant->save();
                                }
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

                $discountAmount = 0;
                $couponId = null;
                if ($request->coupon_code) {
                    $coupon = Coupon::where('code', $request->coupon_code)->lockForUpdate()->first();
                    if (!$coupon || $coupon->status !== 'active') {
                        throw new \Exception("Mã giảm giá không hợp lệ hoặc đã hết hạn.");
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

                $order = Order::create([
                    'order_code'       => 'SORA' . strtoupper(Str::random(8)),
                    'user_id'          => $user->id ?? null,
                    'customer_name'    => $customerName,
                    'customer_phone'   => $customerPhone,
                    'customer_email'   => $request->customer_email,
                    'customer_address' => $customerAddress,
                    'order_note'       => $request->order_note,
                    'sub_total'        => $subTotal,
                    'shipping_fee'     => $shippingFee,
                    'discount_amount'  => $discountAmount,
                    'total_amount'     => $totalAmount,
                    'coupon_id'        => $couponId,
                    'coupon_code'      => $request->coupon_code,
                    'payment_method'   => $request->payment_method,
                    'payment_status'   => 'unpaid',
                    'status'           => 'pending',
                ]);

                foreach ($orderItemsData as $itemData) {
                    $itemData['order_id'] = $order->id;
                    OrderItem::create($itemData);
                }

                OrderStatusHistory::create([
                    'order_id'        => $order->id,
                    'new_status'      => 'pending',
                    'note'            => 'Khách hàng khởi tạo đơn hàng',
                    'changed_by'      => $user->id ?? null,
                    'changed_by_type' => $user ? 'user' : 'guest',
                ]);

                if ($request->payment_method === 'cod') {
                    $cart->items()->delete();
                    $cart->delete();
                    
                    // BÓP CÒ GỬI MAIL CHO ĐƠN COD
                    $this->sendOrderConfirmationEmail($order);

                    return response()->json([
                        'success' => true,
                        'data' => $order,
                        'message' => 'Đặt hàng thành công!'
                    ]);
                }

                if ($request->payment_method === 'momo') {
                    $momoUrl = $this->generateMomoUrl($order);
                    return response()->json([
                        'success' => true,
                        'payment_url' => $momoUrl,
                        'message' => 'Đang chuyển hướng sang Ví MoMo...'
                    ]);
                }
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function generateMomoUrl($order)
    {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";
        
        $partnerCode = 'MOMOBKUN20180529';
        $accessKey   = 'klm05TvNBzhg7h7j';
        $secretKey   = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

        $orderInfo = "Thanh toan don hang SORA " . $order->order_code;
        
        $amount = (string) round($order->total_amount);
        $orderId = $order->order_code . "_" . time(); 
        
        $redirectUrl = 'http://127.0.0.1:8000/api/client/checkout/momo-return';
        $ipnUrl = 'http://127.0.0.1:8000/api/client/checkout/momo-return'; 
        
        $extraData = ""; 
        $requestId = time() . "";
        $requestType = "payWithATM"; 

        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl . "&requestId=" . $requestId . "&requestType=" . $requestType;

        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "SORA Jewelry",
            "storeId"     => "SORA_Store",
            'requestId'   => $requestId,
            'amount'      => (int)$amount, 
            'orderId'     => $orderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'lang'        => 'vi',
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature
        );

        $response = Http::post($endpoint, $data);
        $result = $response->json();

        if (isset($result['payUrl'])) {
            return $result['payUrl'];
        }

        throw new \Exception("MoMo API Error: " . ($result['message'] ?? 'Lỗi tạo link'));
    }

    public function momoReturn(Request $request)
    {
        $parts = explode('_', $request->orderId);
        $orderCode = $parts[0] ?? ''; 
        
        $frontendUrl = 'http://localhost:5173';
        
        if ($request->resultCode == 0) {
            Order::where('order_code', $orderCode)->update(['payment_status' => 'paid']);
            
            // Lấy lại order đầy đủ item để gửi mail
            $order = Order::with('items')->where('order_code', $orderCode)->first();
            if ($order) {
                // BÓP CÒ GỬI MAIL CHO ĐƠN MOMO THÀNH CÔNG
                $this->sendOrderConfirmationEmail($order);
            }

            return redirect($frontendUrl . '/checkout/success?order=' . $orderCode);
        }
        
        $this->cancelOrderAndRestoreStock($orderCode);
        return redirect($frontendUrl . '/checkout/failed?order=' . $orderCode);
    }

    /**
     * HÀM GỬI MAIL AN TOÀN (NẾU LỖI CŨNG KHÔNG LÀM CHẾT WEBSITE)
     */
    private function sendOrderConfirmationEmail($order)
    {
        try {
            // Đảm bảo đã load đủ thông tin items (Sản phẩm) để in ra bảng
            $order->load('items');
            
            // Thực hiện gửi
            Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
            
        } catch (\Exception $e) {
            // Ghi log nếu mail gửi xịt (do sai pass, rớt mạng...) để không báo lỗi 500 cho khách
            Log::error('Lỗi gửi mail xác nhận đơn hàng ' . $order->order_code . ': ' . $e->getMessage());
        }
    }

    private function cancelOrderAndRestoreStock($orderCode)
    {
        $order = Order::with('items')->where('order_code', $orderCode)->first();
        if ($order && $order->status === 'pending' && $order->payment_status === 'unpaid') {
            $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            
            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::where('id', $item->product_variant_id)->increment('stock_quantity', $item->quantity);
                }
            }
        }
    }

    private function resolveCart(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user) return Cart::with(['items.variant', 'items.combo'])->where('user_id', $user->id)->first();
        
        $sessionId = $request->header('X-Cart-Session-Id');
        if ($sessionId) return Cart::with(['items.variant', 'items.combo'])->where('session_id', $sessionId)->first();
        
        return null;
    }
}