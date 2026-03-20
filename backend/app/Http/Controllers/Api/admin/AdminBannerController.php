<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBannerRequest;
use App\Http\Requests\UpdateBannerRequest;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminBannerController extends Controller
{
    private function getNextSortOrder()
    {
        $max = Banner::max('sort_order');
        return is_numeric($max) ? $max + 1 : 1;
    }

    public function index()
    {
        $banners = Banner::withTrashed()
            ->with('brand:id,name,slug')
            ->orderByRaw('sort_order IS NULL, sort_order ASC')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json(['success' => true, 'data' => $banners]);
    }

    public function store(StoreBannerRequest $request)
    {
        $data = $request->validated();

        $data['sort_order'] = ($data['status'] === 'active') ? $this->getNextSortOrder() : null;

        if ($request->hasFile('image_desktop')) {
            $fileDesk = $request->file('image_desktop');
            $fileNameDesk = 'banner_desk_' . Str::slug($data['title']) . '_' . time() . '.' . $fileDesk->getClientOriginalExtension();
            $data['image_desktop'] = $fileDesk->storeAs('banners/desktop', $fileNameDesk, 'public');
        }

        if ($request->hasFile('image_mobile')) {
            $fileMob = $request->file('image_mobile');
            $fileNameMob = 'banner_mob_' . Str::slug($data['title']) . '_' . time() . '.' . $fileMob->getClientOriginalExtension();
            $data['image_mobile'] = $fileMob->storeAs('banners/mobile', $fileNameMob, 'public');
        }

        $banner = Banner::create($data);
        return response()->json(['success' => true, 'message' => 'Tạo banner thành công', 'data' => $banner]);
    }

    public function show($id)
    {
        $banner = Banner::findOrFail($id);
        return response()->json(['success' => true, 'data' => $banner]);
    }

    public function update(UpdateBannerRequest $request, $id)
    {
        $banner = Banner::findOrFail($id);
        $data = $request->validated();

        if ($banner->status !== $data['status']) {
            if ($data['status'] === 'active') {
                $data['sort_order'] = $this->getNextSortOrder();
            } else {
                $data['sort_order'] = null;
            }
        }

        if ($request->hasFile('image_desktop')) {
            $fileDesk = $request->file('image_desktop');
            $fileNameDesk = 'banner_desk_' . Str::slug($data['title']) . '_' . time() . '.' . $fileDesk->getClientOriginalExtension();
            $data['image_desktop'] = $fileDesk->storeAs('banners/desktop', $fileNameDesk, 'public');
            if ($banner->image_desktop) Storage::disk('public')->delete($banner->image_desktop);
        }

        if ($request->hasFile('image_mobile')) {
            $fileMob = $request->file('image_mobile');
            $fileNameMob = 'banner_mob_' . Str::slug($data['title']) . '_' . time() . '.' . $fileMob->getClientOriginalExtension();
            $data['image_mobile'] = $fileMob->storeAs('banners/mobile', $fileNameMob, 'public');
            if ($banner->image_mobile) Storage::disk('public')->delete($banner->image_mobile);
        }

        $banner->update($data);
        return response()->json(['success' => true, 'message' => 'Cập nhật thành công', 'data' => $banner]);
    }

    public function destroy($id)
    {
        $banner = Banner::findOrFail($id);
        $banner->update(['sort_order' => null]);
        $banner->delete();

        return response()->json(['success' => true, 'message' => 'Đã đưa banner vào thùng rác']);
    }

    public function restore($id)
    {
        $banner = Banner::withTrashed()->findOrFail($id);
        $banner->restore();

        if ($banner->status === 'active') {
            $banner->update(['sort_order' => $this->getNextSortOrder()]);
        }

        return response()->json(['success' => true, 'message' => 'Đã khôi phục banner']);
    }

    public function reorder(Request $request)
    {
        $items = $request->items;
        foreach ($items as $item) {
            Banner::where('id', $item['id'])->update(['sort_order' => $item['sort_order']]);
        }
        return response()->json(['success' => true, 'message' => 'Đã cập nhật thứ tự']);
    }
}