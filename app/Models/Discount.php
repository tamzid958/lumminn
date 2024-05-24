<?php

namespace App\Models;

use App\Models\Enum\DiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['name', 'type', 'value', 'active', 'product_id'];

    public function product(): HasOne
    {
        return $this->hasOne(Product::class, "id", "product_id");
    }

    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'active' => 'boolean'
        ];
    }
}
