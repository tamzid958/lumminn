<?php

namespace App\Models;

use App\Models\Enum\PayStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'total_amount',
        'additional_amount',
        'shipping_amount',
        'pay_amount',
        'transaction_amount',
        'pay_status',
        'shipping_status',
        'name',
        'phone_number',
        'address',
        'gateway_response',
        'shipping_id',
        'shipping_provider_id',
        'payment_id',
        'payment_provider_id',
        'note',
        'attachment'
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shippingProvider(): BelongsTo
    {
        return $this->belongsTo(ShippingProvider::class, 'shipping_provider_id');
    }

    public function paymentProvider(): BelongsTo
    {
        return $this->belongsTo(PaymentProvider::class, 'payment_provider_id');
    }

    protected function casts(): array|PayStatus
    {
        return [
            'pay_status' => PayStatus::class,
            'gateway_response' => 'array',
            'note' => 'array',
            'attachment' => 'array',
        ];
    }
}
