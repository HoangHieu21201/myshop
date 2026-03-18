<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id', 
        'name', 
        'slug', 
        'base_price', 
        'promotional_price', 
        'description', 
        'thumbnail_image', 
        'specifications', 
        'is_featured', 
        'status'
    ];

    protected function casts(): array
    {
        return [
            'specifications' => 'array',
            'base_price' => 'decimal:2',
            'promotional_price' => 'decimal:2'
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }
}