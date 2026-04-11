<?php

namespace App\Http\Controllers\Api\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\News;

class ClientNewController extends Controller
{
    // Lấy danh sách bài viết (Có lọc)
    public function index(Request $request)
    {
        $query = News::where('status', 'published');

        // Tìm kiếm theo từ khóa
        if ($request->has('q') && $request->q != '') {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->q . '%')
                  ->orWhere('content', 'like', '%' . $request->q . '%');
            });
        }

        // Lọc theo tác giả
        if ($request->has('author') && $request->author != '') {
            $query->where('author_name', $request->author);
        }

        // Lọc theo danh mục
        if ($request->has('category') && $request->category != '') {
            $query->where('category', $request->category);
        }

        $news = $query->orderBy('created_at', 'desc')->get(); 

        return response()->json([
            'status' => 'success',
            'data' => $news
        ]);
    }

    // Lấy bài viết phổ biến (Sidebar)
    public function popular()
    {
        $popular = News::where('status', 'published')
            ->orderBy('views', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $popular
        ]);
    }

    // Lấy danh sách danh mục động (Dựa trên dữ liệu Admin đã nhập)
    public function categories()
    {
        $categories = News::where('status', 'published')
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->select('category')
            ->distinct()
            ->pluck('category');

        return response()->json([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    // Chi tiết bài viết
    public function show($slug)
    {
        $news = News::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Tăng lượt xem
        $news->increment('views');

        return response()->json([
            'status' => 'success',
            'data' => $news
        ]);
    }
}