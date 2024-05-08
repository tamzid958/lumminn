<?php

namespace App\Models;

use App\Models\Enum\ShippingClass;
use App\Models\Enum\StockStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\VersionStrategy;
use Overtrue\LaravelVersionable\Versionable;

/**
 * @method static find(mixed $productId)
 */
class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Versionable;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'sale_price',
        'production_cost',
        'description',
        'stock_status',
        'stock',
        'is_shipping_charge_applicable',
        'main_photo',
        'photos',
        'meta',
        'production_cost_breakdown'
    ];

    protected $versionable = ['sale_price', 'production_cost', 'production_cost_breakdown'];

    protected $versionStrategy = VersionStrategy::SNAPSHOT;

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    protected function casts(): array|StockStatus
    {
        return [
            'stock_status' => StockStatus::class,
            'photos' => 'array',
            'meta' => 'array',
            'production_cost_breakdown' => 'array'
        ];
    }
}
