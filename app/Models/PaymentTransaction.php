<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'order_id',
        'user_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'paymongo_payment_id',
        'paymongo_payment_intent_id',
        'paymongo_source_id',
        'client_key',
        'checkout_url',
        'metadata',
        'response_data',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'response_data' => 'array',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Generate unique transaction ID
    public static function generateTransactionId()
    {
        return 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(8));
    }

    // Check if payment is successful
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    // Check if payment is pending
    public function isPending()
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    // Check if payment failed
    public function isFailed()
    {
        return in_array($this->status, ['failed', 'cancelled']);
    }
}
