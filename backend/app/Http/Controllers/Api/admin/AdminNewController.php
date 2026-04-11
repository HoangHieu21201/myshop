<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminNewController extends Controller
{
    /**
     * Lấy danh sách tin tức cho Admin
     */
    public function index()
    {
        try {
            $news = News::orderBy('created_at', 'desc')->get();
            return response()->json([
                'status' => 'success',
                'data' => $news
            ], 200);
        } catch (\Throwable $e) { 
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi server: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lưu bài viết mới
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'             => 'required|string|max:255',
                'slug'              => 'required|string|unique:news,slug',
                'excerpt'           => 'nullable|string',
                'content'           => 'required|string',
                'author_name'       => 'required|string|max:100',
                'category'          => 'nullable|string',
                'status'            => 'required|in:pending,published,draft',
                'meta_title'        => 'nullable|string|max:255',
                'meta_description'  => 'nullable|string',
                'meta_keywords'     => 'nullable|string|max:255',
                'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            ]);

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('news', 'public');
                $validated['image_url'] = '/storage/' . $path;
            }

            $news = News::create($validated);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Tạo bài viết thành công', 
                'data' => $news
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật bài viết
     */
    public function update(Request $request, $id)
    {
        try {
            $news = News::findOrFail($id);

            $validated = $request->validate([
                'title'             => 'required|string|max:255',
                'slug'              => ['required', 'string', Rule::unique('news')->ignore($news->id)],
                'excerpt'           => 'nullable|string',
                'content'           => 'required|string',
                'author_name'       => 'required|string|max:100',
                'category'          => 'nullable|string',
                'status'            => 'required|in:pending,published,draft',
                'meta_title'        => 'nullable|string|max:255',
                'meta_description'  => 'nullable|string',
                'meta_keywords'     => 'nullable|string|max:255',
                'image'             => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            ]);

            if ($request->hasFile('image')) {
                // Xóa ảnh cũ nếu tồn tại
                if ($news->image_url) {
                    $oldPath = str_replace('/storage/', '', $news->image_url);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('image')->store('news', 'public');
                $validated['image_url'] = '/storage/' . $path;
            }

            $news->update($validated);

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật thành công', 
                'data' => $news
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Xóa bài viết
     */
    public function destroy($id)
    {
        try {
            $news = News::findOrFail($id);
            
            if ($news->image_url) {
                $oldPath = str_replace('/storage/', '', $news->image_url);
                Storage::disk('public')->delete($oldPath);
            }

            $news->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Xóa bài viết thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật nhanh trạng thái (Duyệt/Ẩn)
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate(['status' => 'required|in:pending,published,draft']);
            
            $news = News::findOrFail($id);
            $news->status = $request->status;
            $news->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Cập nhật trạng thái thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi: ' . $e->getMessage()
            ], 500);
        }
    }
}