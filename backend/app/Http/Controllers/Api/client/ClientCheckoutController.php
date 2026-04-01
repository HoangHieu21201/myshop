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
use App\Models\Combo;
use App\Models\Admin;
use App\Http\Requests\UserCheckoutRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPlacedMail;
use App\Mail\AdminNewOrderMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache; // BỔ SUNG THƯ VIỆN CACHE ĐỂ DÙNG REDIS LOCK

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
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
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
        $sessionId = $request->header('X-Cart-Session-Id');

        // BỌC REDIS LOCK: Ngăn chặn click đúp, chống tạo trùng đơn hàng cùng lúc
        $lockKey = 'checkout_lock_' . ($user ? $user->id : $sessionId);
        $lock = Cache::lock($lockKey, 10); // Tạo ổ khóa 10 giây cho User/Session này

        if (!$lock->get()) {
            // Bị đá ra ngay lập tức nếu cố tình spam click, không tốn 1 nhịp chọc vào DB nào cả!
            return response()->json([
                'success' => false,
                'message' => 'Hệ thống đang xử lý đơn hàng của bạn, vui lòng không bấm liên tục...'
            ], 429);
        }

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

                //  LẤY TOÀN BỘ CÁC ID CẦN LOCK
                $variantIdsToLock = [];
                $comboIdsToLock = [];

                foreach ($cart->items as $item) {
                    if ($item->product_variant_id) {
                        $variantIdsToLock[] = $item->product_variant_id;
                    } elseif ($item->combo_id) {
                        $comboIdsToLock[] = $item->combo_id;
                        // Gom ID của các món khách tự chọn
                        if (is_array($item->combo_selections)) {
                            $variantIdsToLock = array_merge($variantIdsToLock, array_column($item->combo_selections, 'selected_variant_id'));
                        }
                    }
                }

                // Lấy các Combo tham gia trong giỏ hàng (Khóa luôn Combo để xử lý giới hạn mua - usage_limit)
                $combos = Combo::with(['items' => function ($q) {
                    $q->whereNotNull('product_variant_id'); // Chỉ lấy các món cố định
                }])->whereIn('id', array_unique($comboIdsToLock))->lockForUpdate()->get()->keyBy('id');

                // Gom thêm ID của các món cố định (trong Combo) vào danh sách cần khóa kho
                foreach ($combos as $combo) {
                    foreach ($combo->items as $cItem) {
                        if ($cItem->product_variant_id) {
                            $variantIdsToLock[] = $cItem->product_variant_id;
                        }
                    }
                }

                // Khóa tất cả các Variants liên quan (chống Race Condition cấp độ Database)
                $variants = ProductVariant::whereIn('id', array_unique($variantIdsToLock))
                    ->orderBy('id')->lockForUpdate()->get()->keyBy('id');

                $subTotal = 0;
                $orderItemsData = [];

                // TIẾN HÀNH TRỪ KHO VÀ TẠO DATA ITEMS
                foreach ($cart->items as $item) {

                    // --- XỬ LÝ SẢN PHẨM LẺ ---
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
                    }

                    // --- XỬ LÝ COMBO ĐẶC QUYỀN ---
                    elseif ($item->combo_id) {
                        $combo = $combos->get($item->combo_id);
                        if (!$combo) {
                            throw new \Exception("Combo không tồn tại hoặc đã ngừng kinh doanh.");
                        }

                        // 1. Trừ giới hạn mua (usage_limit) của Combo
                        if ($combo->usage_limit !== null) {
                            if ($combo->usage_limit < $item->quantity) {
                                throw new \Exception("Gói ưu đãi {$combo->name} đã vượt quá số lượt bán cho phép.");
                            }
                            $combo->usage_limit -= $item->quantity;
                            $combo->save();
                        }

                        // 2. Trừ kho các mặt hàng KHÁCH TỰ CHỌN
                        if (is_array($item->combo_selections)) {
                            foreach ($item->combo_selections as $selection) {
                                $vId = $selection['selected_variant_id'] ?? null;
                                if ($vId) {
                                    $variant = $variants->get($vId);
                                    if (!$variant || $variant->stock_quantity < $item->quantity) {
                                        throw new \Exception("Một sản phẩm tự chọn trong bộ {$combo->name} đã hết hàng.");
                                    }
                                    $variant->stock_quantity -= $item->quantity;
                                    $variant->save();
                                }
                            }
                        }

                        // 3. Trừ kho các mặt hàng CỐ ĐỊNH do Shop cài đặt
                        foreach ($combo->items as $cItem) {
                            if ($cItem->product_variant_id) {
                                $variant = $variants->get($cItem->product_variant_id);
                                // Số lượng cần trừ = SL Combo mua * SL món đó quy định trong Combo
                                $totalQtyNeeded = $item->quantity * $cItem->quantity;

                                if (!$variant || $variant->stock_quantity < $totalQtyNeeded) {
                                    throw new \Exception("Sản phẩm cố định trong bộ {$combo->name} đã hết hàng.");
                                }
                                $variant->stock_quantity -= $totalQtyNeeded;
                                $variant->save();
                            }
                        }

                        $itemTotal = $item->subtotal;
                        $subTotal += $itemTotal;

                        $orderItemsData[] = [
                            'product_id'         => null,
                            'product_variant_id' => null,
                            'product_name'       => $combo->name,
                            'variant_sku'        => 'COMBO-' . $item->combo_id,
                            'variant_attributes' => null,
                            'variant_image'      => $combo->thumbnail_image,
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

                // LƯU ĐƠN HÀNG VÀ DỌN DẸP
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

                    // GỬI MAIL CHO KHÁCH & ADMIN
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
        } finally {
            // LUÔN MỞ KHÓA REDIS khi kết thúc tiến trình (bất kể thành công hay bị Exception ở trong catch)
            $lock->release();
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

            $order = Order::with('items')->where('order_code', $orderCode)->first();
            if ($order) {
                $this->sendOrderConfirmationEmail($order);
            }

            return redirect($frontendUrl . '/checkout/success?order=' . $orderCode);
        }

        $this->cancelOrderAndRestoreStock($orderCode);
        return redirect($frontendUrl . '/checkout/failed?order=' . $orderCode);
    }

    private function sendOrderConfirmationEmail($order)
    {
        try {
            $order->load('items');

            if (!empty($order->customer_email)) {
                Mail::to($order->customer_email)->send(new OrderPlacedMail($order));
            }

            $adminEmailsEnv = env('ADMIN_ORDER_NOTIFICATION_EMAIL');
            $adminEmailsToNotify = [];

            if (!empty($adminEmailsEnv)) {
                $adminEmailsToNotify = array_map('trim', explode(',', $adminEmailsEnv));
                $adminEmailsToNotify = array_filter($adminEmailsToNotify);
            } else {
                $adminEmailsToNotify = Admin::where('role_id', 1)
                    ->where('status', 'active')
                    ->pluck('email')
                    ->toArray();
            }

            if (!empty($adminEmailsToNotify)) {
                Mail::to($adminEmailsToNotify)->send(new AdminNewOrderMail($order));
            }
        } catch (\Exception $e) {
            Log::error('Lỗi gửi mail xác nhận đơn hàng ' . $order->order_code . ': ' . $e->getMessage());
        }
    }

    private function cancelOrderAndRestoreStock($orderCode)
    {
        $order = Order::with('items')->where('order_code', $orderCode)->first();

        if ($order && $order->status === 'pending' && $order->payment_status === 'unpaid') {
            $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);

            foreach ($order->items as $item) {

                // --- TRẢ KHO SẢN PHẨM LẺ ---
                if ($item->product_variant_id) {
                    ProductVariant::where('id', $item->product_variant_id)->increment('stock_quantity', $item->quantity);
                }

                // --- TRẢ KHO COMBO ---
                elseif ($item->combo_id) {
                    // 1. Trả lượt giới hạn Combo (usage_limit)
                    Combo::where('id', $item->combo_id)
                        ->whereNotNull('usage_limit')
                        ->increment('usage_limit', $item->quantity);

                    // 2. Trả lại kho của các món Khách tự chọn
                    if (is_array($item->combo_selections)) {
                        foreach ($item->combo_selections as $selection) {
                            $vId = $selection['selected_variant_id'] ?? null;
                            if ($vId) {
                                ProductVariant::where('id', $vId)->increment('stock_quantity', $item->quantity);
                            }
                        }
                    }

                    // 3. Trả lại kho của các món Cố định trong Combo
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

    private function resolveCart(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user) return Cart::with(['items.variant', 'items.combo'])->where('user_id', $user->id)->first();

        $sessionId = $request->header('X-Cart-Session-Id');
        if ($sessionId) return Cart::with(['items.variant', 'items.combo'])->where('session_id', $sessionId)->first();

        return null;
    }
}
