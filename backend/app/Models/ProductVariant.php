<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 

class ProductVariant extends Model
{
    use SoftDeletes;

    protected $table = 'product_variants';
    
    protected $fillable = [
        'product_id', 
        'sku', 
        'price', 
        'promotional_price', 
        'stock_quantity', 
        'image_url', 
        'is_default'
    ];

    protected $casts = [
        'price' => 'decimal:2', 
        'promotional_price' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeValues()
    {
        return $this->belongsToMany(AttributeValue::class, 'product_variant_attributes', 'variant_id', 'attribute_value_id');
    }
}