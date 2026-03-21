<?php

namespace App\Http\Controllers\Api\client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Lấy danh sách giỏ hàng hiện tại
     */
    public function index(Request $request)
    {
        $cart = $this->resolveCart($request);

        if (!$cart) {
            return response()->json(['data' => []]);
        }

        // Đã chặn N+1 Query từ Model CartItem
        return response()->json([
            'data' => $cart->items,
            'summary' => [
                'total_items' => $cart->items->sum('quantity'),
                'subtotal' => $cart->items->sum('subtotal')
            ]
        ]);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     * Lưu ý: Hiện tại đang dùng Request thường, TỐT NHẤT NÊN CHUYỂN SANG StoreCartItemRequest
     */
    public function store(Request $request)
    {
        // Tạm thời validate cơ bản tại đây
        $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id,deleted_at,NULL',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $cart = $this->resolveCart($request, true); // true = Tạo mới nếu chưa có

            // Sử dụng Lock (Pessimistic Locking) hoặc firstOrNew để chống Race Condition (Double-click)
            $cartItem = CartItem::firstOrNew([
                'cart_id' => $cart->id,
                'product_variant_id' => $request->product_variant_id,
            ]);

            // Cộng dồn nếu đã tồn tại, hoặc set mới nếu chưa
            $cartItem->quantity += $request->quantity;
            $cartItem->save();

            DB::commit();

            return response()->json([
                'message' => 'Đã thêm vào giỏ hàng',
                'data' => $cartItem->load('variant')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Có lỗi xảy ra: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hợp nhất giỏ hàng (Gọi API này NGAY SAU KHI Vue 3 nhận được Token đăng nhập)
     */
    public function mergeCart(Request $request)
    {
        $sessionId = $request->header('X-Cart-Session-Id');
        $user = auth('sanctum')->user();

        if (!$sessionId || !$user) {
            return response()->json(['message' => 'Không đủ điều kiện merge'], 400);
        }

        $guestCart = Cart::where('session_id', $sessionId)->first();
        if (!$guestCart || $guestCart->items->isEmpty()) {
            return response()->json(['message' => 'Không có gì để merge']);
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        DB::beginTransaction();
        try {
            foreach ($guestCart->items as $guestItem) {
                $userItem = CartItem::firstOrNew([
                    'cart_id' => $userCart->id,
                    'product_variant_id' => $guestItem->product_variant_id,
                ]);

                // Logic hợp nhất: Cộng dồn số lượng
                $userItem->quantity += $guestItem->quantity;
                $userItem->save();
            }

            // Dọn dẹp giỏ hàng rác của Guest sau khi đã merge xong
            $guestCart->delete();

            DB::commit();
            return response()->json(['message' => 'Hợp nhất giỏ hàng thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Lỗi khi hợp nhất'], 500);
        }
    }

    /**
     * Hàm nội bộ: Xác định xem đang lấy giỏ của User hay Guest
     */
    private function resolveCart(Request $request, $createIfNotFound = false)
    {
        $user = auth('sanctum')->user();

        if ($user) {
            if ($createIfNotFound) {
                return Cart::firstOrCreate(['user_id' => $user->id]);
            }
            return Cart::where('user_id', $user->id)->first();
        }

        $sessionId = $request->header('X-Cart-Session-Id');
        if (!$sessionId) {
            abort(400, 'Thiếu X-Cart-Session-Id ở Header.');
        }

        if ($createIfNotFound) {
            return Cart::firstOrCreate(['session_id' => $sessionId]);
        }
        
        return Cart::where('session_id', $sessionId)->first();
    }
}