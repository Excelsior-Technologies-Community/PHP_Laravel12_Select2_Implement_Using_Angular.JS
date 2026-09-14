<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'price',
        'sku',
        'slug',
        'category_id',
        'brand_id',
        'stock_quantity',
        'status',
        'discount',
        'image',
    ];

    protected $casts = [
        'price' => 'integer',
        'discount' => 'decimal:2',
        'stock_quantity' => 'integer',
    ];

    protected $appends = ['final_price'];

    public function colors()
    {
        return $this->belongsToMany(Color::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function getFinalPriceAttribute()
    {
        return round($this->price - ($this->price * ((float) $this->discount / 100)), 2);
    }
}