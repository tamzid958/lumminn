<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'sale_price',
        'production_cost',
        'description',
        'stock_status',
        'stock',
        'is_shipping_charge_applicable'
    ];

    protected $casts = [
        'main_photo' => 'string',
        'photos' => 'array',
        'meta' => 'array',
        'production_cost_breakdown' => 'array'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
