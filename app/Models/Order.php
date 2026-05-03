<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'sub_total',
        'discount_amount',
        'shipping_amount',
        'tax_amount',
        'grand_total',
        'payment_method',
        'payment_status',
        'status',
        'transaction_ref',
        'payment_failure_reason',
        'coupon_code',
        'loyalty_points_used',
        'loyalty_discount_amount',
        'gift_card_code',
        'gift_card_amount',
        'invoice_sent_at',
        'customer_name',
        'customer_email',
        'customer_phone',
        'shipping_address',
        'shipping_zone',
        'courier_name',
        'tracking_number',
        'estimated_delivery_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'sub_total' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'shipping_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'loyalty_discount_amount' => 'decimal:2',
            'gift_card_amount' => 'decimal:2',
            'invoice_sent_at' => 'datetime',
            'estimated_delivery_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    public function returns(): HasMany
    {
        return $this->hasMany(OrderReturn::class);
    }

    public function paymentEvents(): HasMany
    {
        return $this->hasMany(PaymentEvent::class);
    }
}
