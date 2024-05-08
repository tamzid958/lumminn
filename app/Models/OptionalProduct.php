<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelVersionable\Versionable;
use Overtrue\LaravelVersionable\VersionStrategy;

class OptionalProduct extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Versionable;

    protected $fillable = [
        'title',
        'sale_price',
        'production_cost',
    ];

    protected $versionable = ['sale_price', 'production_cost'];

    protected $versionStrategy = VersionStrategy::SNAPSHOT;
}
