<?php

namespace App\Http\Controllers\Api\client;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\ProductVariant;
use App\Http\Requests\Cart\StoreCartItemRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * [YÊU CẦU: XEM GIỎ HÀNG]
     * Trả về danh sách sản phẩm và tóm tắt (Tổng lượng, Tạm tính, Tổng tiền)
     */
    public function index(Request $request)
    {
        $cart = $this->resolveCart($request);

        if (!$cart) {
            return response()->json([
                'success' => true, 
                'data' => [],
                'summary' => ['total_items' => 0, 'subtotal' => 0]
            ]);
        }

        // Tự động load 'variant' (nhờ $with trong model CartItem)
        return response()->json([
            'success' => true,
            'data' => $cart->items,
            'summary' => [
                'total_items' => $cart->items->sum('quantity'),
                'subtotal'    => $cart->items->sum('subtotal') // Accessor subtotal từ Model
            ]
        ]);
    }

    /**
     * [YÊU CẦU: THÊM VÀO GIỎ HÀNG]
     * Chống thêm sản phẩm hết hàng và vượt tồn kho (Validate trong StoreCartItemRequest)
     */
    public function store(StoreCartItemRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                $cart = $this->resolveCart($request, true);

                // Khóa dòng biến thể để đảm bảo tồn kho thực tế (Pessimistic Locking)
                $variant = ProductVariant::lockForUpdate()->findOrFail($request->product_variant_id);

                // Tìm hoặc tạo mới item trong giỏ
                $cartItem = CartItem::firstOrNew([
                    'cart_id'            => $cart->id,
                    'product_variant_id' => $variant->id,
                ]);

                $newQuantity = $cartItem->quantity + $request->quantity;

                // Kiểm tra tồn kho lần cuối trong Transaction
                if ($newQuantity > $variant->stock_quantity) {
                    throw new \Exception("Số lượng yêu cầu vượt quá tồn kho hiện có.");
                }

                $cartItem->quantity = $newQuantity;
                $cartItem->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Đã thêm sản phẩm vào giỏ hàng.',
                    'data'    => $cartItem->load('variant')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * [YÊU CẦU: TĂNG/GIẢM SỐ LƯỢNG]
     * Sử dụng UpdateCartItemRequest để check tồn kho live
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem)
    {
        // Route model binding tự động tìm $cartItem qua ID trên URL
        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật số lượng thành công.',
            'data'    => $cartItem
        ]);
    }

    /**
     * [YÊU CẦU: XÓA 1 SẢN PHẨM]
     */
    public function destroy(CartItem $cartItem)
    {
        $cartItem->delete();
        return response()->json([
            'success' => true, 
            'message' => 'Đã xóa sản phẩm khỏi giỏ hàng.'
        ]);
    }

    /**
     * [YÊU CẦU: XÓA TOÀN BỘ GIỎ]
     */
    public function clear(Request $request)
    {
        $cart = $this->resolveCart($request);

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Giỏ hàng đã được làm trống.'
        ]);
    }

    /**
     * [YÊU CẦU: ĐỒNG BỘ KHI ĐĂNG NHẬP]
     * Hợp nhất sản phẩm từ Session của Guest vào Account của User
     */
    public function mergeCart(Request $request)
    {
        $sessionId = $request->header('X-Cart-Session-Id');
        $user = auth('sanctum')->user();

        if (!$sessionId || !$user) {
            return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ.'], 400);
        }

        $guestCart = Cart::where('session_id', $sessionId)->first();
        
        if (!$guestCart || $guestCart->items->isEmpty()) {
            return response()->json(['success' => true, 'message' => 'Không có dữ liệu để hợp nhất.']);
        }

        return DB::transaction(function () use ($guestCart, $user) {
            $userCart = Cart::firstOrCreate(['user_id' => $user->id]);

            foreach ($guestCart->items as $guestItem) {
                $userItem = CartItem::firstOrNew([
                    'cart_id'            => $userCart->id,
                    'product_variant_id' => $guestItem->product_variant_id,
                ]);

                // Cộng dồn số lượng
                $userItem->quantity += $guestItem->quantity;
                $userItem->save();
            }

            // Xóa giỏ guest sau khi đã chuyển dữ liệu xong
            $guestCart->delete();

            return response()->json([
                'success' => true, 
                'message' => 'Đã đồng bộ giỏ hàng vào tài khoản của bạn.'
            ]);
        });
    }

    /**
     * HELPER: Giải quyết giỏ hàng theo User hoặc Session
     * Ưu tiên User ID nếu đã đăng nhập (Sanctum)
     */
    private function resolveCart(Request $request, $createIfNotFound = false)
    {
        $user = auth('sanctum')->user();

        // 1. Nếu đã đăng nhập
        if ($user) {
            return $createIfNotFound 
                ? Cart::firstOrCreate(['user_id' => $user->id]) 
                : Cart::where('user_id', $user->id)->first();
        }

        // 2. Nếu là khách (Guest) - Dựa vào Header X-Cart-Session-Id từ Vue
        $sessionId = $request->header('X-Cart-Session-Id');
        
        if (!$sessionId) {
            if ($createIfNotFound) abort(400, 'Thiếu Session ID để định danh giỏ hàng.');
            return null;
        }

        return $createIfNotFound 
            ? Cart::firstOrCreate(['session_id' => $sessionId]) 
            : Cart::where('session_id', $sessionId)->first();
    }
}