<?php

namespace App\Http\Controllers\Api\admin;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AdminAttributeController extends Controller
{
    public function index()
    {
        // Lấy tất cả thuộc tính kèm theo các giá trị (values) của nó
        $attributes = Attribute::with('values')->orderBy('id', 'desc')->get();
        return response()->json(['success' => true, 'data' => $attributes]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:attributes,name']);
        $attribute = Attribute::create(['name' => $request->name]);
        $attribute->load('values'); // Load mảng values rỗng để Vue không bị lỗi undefined
        
        return response()->json(['success' => true, 'data' => $attribute]);
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:255|unique:attributes,name,' . $id]);
        $attribute = Attribute::findOrFail($id);
        $attribute->update(['name' => $request->name]);
        
        return response()->json(['success' => true, 'data' => $attribute]);
    }

    public function destroy($id)
    {
        $attribute = Attribute::findOrFail($id);
        $attribute->delete(); // Cascade sẽ tự động xóa values
        
        return response()->json(['success' => true, 'message' => 'Đã xóa thuộc tính']);
    }
}