<?php

namespace App\Http\Controllers\Api\client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
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
            return response()->json(['success' => true, 'data' => []]);
        }

        return response()->json([
            'success' => true,
            'data' => $cart->items,
            'summary' => [
                'total_items' => $cart->items->sum('quantity'),
                'subtotal'    => $cart->items->sum('subtotal')
            ]
        ]);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng
     */
    public function store(StoreCartItemRequest $request)
    {
        try {
            DB::beginTransaction();

            $cart = $this->resolveCart($request, true);

            $cartItem = CartItem::firstOrNew([
                'cart_id'            => $cart->id,
                'product_variant_id' => $request->product_variant_id,
            ]);

            $cartItem->quantity += $request->quantity;
            $cartItem->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Đã thêm vào giỏ hàng',
                'data'    => $cartItem->load('variant')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Cập nhật số lượng trực tiếp (dùng cho trang Giỏ hàng)
     */
    public function update(UpdateCartItemRequest $request, CartItem $cart_item)
    {
        $cart_item->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số lượng thành công',
            'data'    => $cart_item
        ]);
    }

    /**
     * Xóa món hàng khỏi giỏ
     */
    public function destroy(CartItem $cart_item)
    {
        $cart_item->delete();
        return response()->json(['success' => true, 'message' => 'Đã xóa sản phẩm khỏi giỏ hàng']);
    }

    /**
     * Hợp nhất giỏ hàng Guest -> User
     */
    public function mergeCart(Request $request)
    {
        $sessionId = $request->header('X-Cart-Session-Id');
        $user = auth('sanctum')->user();

        if (!$sessionId || !$user) {
            return response()->json(['success' => false, 'message' => 'Thiếu thông tin định danh'], 400);
        }

        $guestCart = Cart::where('session_id', $sessionId)->first();
        if (!$guestCart || $guestCart->items->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'Không có dữ liệu để hợp nhất']);
        }

        $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

        DB::beginTransaction();
        try {
            foreach ($guestCart->items as $guestItem) {
                $userItem = CartItem::firstOrNew([
                    'cart_id'            => $userCart->id,
                    'product_variant_id' => $guestItem->product_variant_id,
                ]);

                $userItem->quantity += $guestItem->quantity;
                $userItem->save();
            }

            $guestCart->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Hợp nhất giỏ hàng thành công']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Helper xác định giỏ hàng
     */
    private function resolveCart(Request $request, $createIfNotFound = false)
    {
        $user = auth('sanctum')->user();

        if ($user) {
            return $createIfNotFound 
                ? Cart::firstOrCreate(['user_id' => $user->id]) 
                : Cart::where('user_id', $user->id)->first();
        }

        $sessionId = $request->header('X-Cart-Session-Id');
        if (!$sessionId) {
            abort(400, 'Thiếu X-Cart-Session-Id ở Header.');
        }

        return $createIfNotFound 
            ? Cart::firstOrCreate(['session_id' => $sessionId]) 
            : Cart::where('session_id', $sessionId)->first();
    }
}