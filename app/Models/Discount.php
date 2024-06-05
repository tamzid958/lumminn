<?php

namespace App\Models;

use App\Models\Enum\DiscountType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Discount extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['code', 'type', 'value', 'free_shipping', 'active'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    protected function casts(): array
    {
        return [
            'type' => DiscountType::class,
            'active' => 'boolean',
            'free_shipping' => 'boolean'
        ];
    }
}
