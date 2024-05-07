<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShippingProvider extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'inside_dhaka_charge',
        'outside_dhaka_charge',
        'meta'
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }
}
