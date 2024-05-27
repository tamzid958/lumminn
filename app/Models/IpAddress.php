<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IpAddress extends Model
{
    use HasFactory;

    protected $fillable = ['ip', 'count', 'alias', 'is_blocked'];

    public function order(): HasMany
    {
        return $this->hasMany(Order::class, "id", "ip_address_id");
    }
    
    protected function casts(): array
    {
        return [
            'is_blocked' => 'boolean'
        ];
    }
}
