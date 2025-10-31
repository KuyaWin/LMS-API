<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_number',
        'user_id',
        'pickup_date',
        'pickup_time',
        'pickup_address',
        'is_rush_service',
        'special_instructions',
        'promo_code',
        'discount_amount',
        'subtotal',
        'rush_fee',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'paid_at',
        'paymongo_payment_id',
        'paymongo_payment_intent_id',
        'transaction_id',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'pickup_time' => 'datetime:H:i',
        'is_rush_service' => 'boolean',
        'discount_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'rush_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that owns the order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order items for the order.
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the payment transaction for the order.
     */
    public function transaction()
    {
        return $this->hasOne(PaymentTransaction::class);
    }

    /**
     * Generate unique order number.
     */
    public static function generateOrderNumber()
    {
        $year = date('Y');
        $lastOrder = self::whereYear('created_at', $year)->orderBy('id', 'desc')->first();
        $number = $lastOrder ? (int)substr($lastOrder->order_number, -3) + 1 : 1;
        return 'ORD-' . $year . '-' . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}

