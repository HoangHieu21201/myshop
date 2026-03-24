<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComboItem extends Model
{
    protected $table = 'combo_items';

    protected $fillable = [
        'combo_id', 'product_id', 'product_variant_id', 'quantity'
    ];

    public function combo()
    {
        return $this->belongsTo(Combo::class, 'combo_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Biến thể cố định (Nếu null -> Khách tự chọn)
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}