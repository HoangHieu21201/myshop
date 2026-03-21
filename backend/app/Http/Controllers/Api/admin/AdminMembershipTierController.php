<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\MembershipTier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AdminMembershipTierController extends Controller
{
    /**
     * Lấy danh sách các Hạng thành viên (Sắp xếp theo hạn mức từ thấp đến cao)
     */
    public function index()
    {
        // Kèm theo số lượng user đang ở hạng này để giao diện hiển thị cho Admin thấy
        $tiers = MembershipTier::withCount('users')
                    ->orderBy('min_spent', 'asc')
                    ->get();

        return response()->json([
            'status' => 'success',
            'data' => $tiers
        ]);
    }

    /**
     * Tạo Hạng thành viên mới
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:membership_tiers,name',
            'min_spent' => 'required|numeric|min:0',
            'min_orders' => 'required|integer|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'yearly_service_quota' => 'required|integer|min:0',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // Icon tối đa 2MB
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $data = $request->except('icon');

        // Xử lý upload icon (giống như bạn đã làm ở Banner)
        if ($request->hasFile('icon')) {
            $path = $request->file('icon')->store('tiers', 'public');
            $data['icon'] = $path;
        }

        $tier = MembershipTier::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Đã tạo hạng thành viên thành công.',
            'data' => $tier
        ], 201);
    }

    /**
     * Lấy chi tiết 1 Hạng
     */
    public function show($id)
    {
        $tier = MembershipTier::find($id);
        if (!$tier) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy hạng này'], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $tier
        ]);
    }

    /**
     * Cập nhật Hạng thành viên
     */
    public function update(Request $request, $id)
    {
        $tier = MembershipTier::find($id);
        if (!$tier) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy hạng này'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50|unique:membership_tiers,name,' . $id,
            'min_spent' => 'required|numeric|min:0',
            'min_orders' => 'required|integer|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'yearly_service_quota' => 'required|integer|min:0',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 422);
        }

        $data = $request->except('icon', '_method');

        if ($request->hasFile('icon')) {
            // Xóa icon cũ nếu có
            if ($tier->icon && Storage::disk('public')->exists($tier->icon)) {
                Storage::disk('public')->delete($tier->icon);
            }
            $path = $request->file('icon')->store('tiers', 'public');
            $data['icon'] = $path;
        }

        $tier->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Cập nhật thành công.',
            'data' => $tier
        ]);
    }

    /**
     * Xóa Hạng (Đã được bọc thép chống lỗi xóa nhầm)
     */
    public function destroy($id)
    {
        $tier = MembershipTier::withCount('users')->find($id);
        
        if (!$tier) {
            return response()->json(['status' => 'error', 'message' => 'Không tìm thấy hạng này'], 404);
        }

        // Đòn chặn số 1: Không cho xóa Hạng Gốc (Mặc định)
        if ($tier->min_spent == 0) {
            return response()->json(['status' => 'error', 'message' => 'Không thể xóa Hạng Mặc định (0 đồng) của hệ thống!'], 403);
        }

        // Đòn chặn số 2: Không cho xóa Hạng đang có khách hàng
        if ($tier->users_count > 0) {
            return response()->json([
                'status' => 'error', 
                'message' => "Không thể xóa! Đang có {$tier->users_count} khách hàng thuộc hạng này. Vui lòng hạ hạng của họ trước."
            ], 403);
        }

        // Xóa icon vật lý
        if ($tier->icon && Storage::disk('public')->exists($tier->icon)) {
            Storage::disk('public')->delete($tier->icon);
        }

        $tier->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Đã xóa hạng thành viên.'
        ]);
    }
}