<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use App\Models\Combo;
use App\Models\Product;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ClientComboController extends Controller
{
    public function index(Request $request)
    {
        $yesterday = Carbon::now()->subDay();

        $query = Combo::with(['items.product', 'items.variant'])
            ->where('status', 'active')
            ->where(function($q) use ($yesterday) {
                $q->whereNull('end_date')
                  ->orWhere('end_date', '>=', $yesterday);
            });

        if ($request->has('gender') && $request->gender !== 'all' && $request->gender !== '') {
            $query->where('target_gender', $request->gender);
        }

        $combos = $query->orderByRaw('CASE WHEN end_date IS NOT NULL AND end_date < NOW() THEN 1 ELSE 0 END ASC')
            ->orderBy('id', 'desc')
            ->paginate(12);

        return response()->json([
            'success' => true, 
            'data' => $combos
        ]);
    }

    public function show($slug)
    {
        $now = Carbon::now();

        $combo = Combo::with([
            'items.product' => function($q) {
                $q->with([
                    'category:id,name,slug',
                    'brand:id,name',
                    'variants' => function($vq) {
                        $vq->where('stock_quantity', '>', 0)
                           ->with(['attributeValues.attribute']);
                    }
                ]);
            },
            'items.variant' => function($vq) {
                $vq->with(['attributeValues.attribute', 'product.category', 'product.brand']);
            }
        ])
        ->where('slug', $slug)
        ->where('status', 'active')
        ->firstOrFail();

        $categoryIds = collect($combo->items)->map(function($item) {
            return $item->product ? $item->product->category_id : null;
        })->filter()->unique()->toArray();

        $productIdsInCombo = collect($combo->items)->map(function($item) {
            return $item->product_id;
        })->filter()->unique()->toArray();

        $relatedProducts = Product::with(['category:id,name,slug'])
            ->whereIn('category_id', $categoryIds)
            ->whereNotIn('id', $productIdsInCombo)
            ->where('status', 'published')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        foreach ($combo->items as $item) {
            if ($item->product && $item->product->variants) {
                foreach ($item->product->variants as $variant) {
                    $attrMap = [];
                    if ($variant->attributeValues) {
                        foreach ($variant->attributeValues as $val) {
                            if ($val->attribute) {
                                $attrMap[$val->attribute->name] = $val->value;
                            }
                        }
                    }
                    if (empty($attrMap)) {
                        $attrMap['Phiên bản'] = $variant->sku;
                    }
                    $variant->formatted_attributes = $attrMap;
                    unset($variant->attributeValues);
                }
            }
            if ($item->variant) {
                $attrMap = [];
                if ($item->variant->attributeValues) {
                    foreach ($item->variant->attributeValues as $val) {
                        if ($val->attribute) {
                            $attrMap[$val->attribute->name] = $val->value;
                        }
                    }
                }
                if (empty($attrMap)) {
                    $attrMap['Phiên bản'] = $item->variant->sku;
                }
                $item->variant->formatted_attributes = $attrMap;
                unset($item->variant->attributeValues);
            }
        }

        return response()->json([
            'success' => true, 
            'data' => $combo,
            'related_products' => $relatedProducts
        ]);
    }
}