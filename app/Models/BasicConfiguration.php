<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BasicConfiguration extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = ['config_key', 'config_value'];
}
