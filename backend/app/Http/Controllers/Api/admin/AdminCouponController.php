<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCouponRequest;
use App\Http\Requests\UpdateCouponRequest;
use Illuminate\Http\Request;
use App\Models\Coupon;

class AdminCouponController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $coupon = Coupon::withTrashed()->orderBy('id', 'desc')->get();

        return response()->json($coupon);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCouponRequest $request)
    {
        try {
            $data = $request->validated();

            $coupon = Coupon::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Tạo mã giảm giá thành công!',
                'data' => $coupon
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $coupon = Coupon::findOrFail($id);

        return response()->json(['success' => true, 'data' => $coupon]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCouponRequest $request, string $id)
    {
        $coupon = Coupon::findOrFail($id);

        try {
            $data = $request->validated();
            $coupon->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật mã giảm giá thành công!',
                'data' => $coupon
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $coupon = Coupon::findOrFail($id);

        try {
            $coupon->delete($coupon);

            return response()->json([
                'success' => true,
                'message' => 'Xoá mã giảm giá thành công!',
                'data' => $coupon
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
        }
    }
}
