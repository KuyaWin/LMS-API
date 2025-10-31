<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'service_id',
        'quantity',
        'unit_price',
        'total_price',
        'addons',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'addons' => 'array',
    ];

    // Hardcoded addon prices (matches .NET MAUI app constants)
    private static $addonPrices = [
        1 => ['name' => 'Extra Soap', 'price' => 10.00],
        2 => ['name' => 'Extra Fabric Conditioner', 'price' => 10.00],
        3 => ['name' => 'Bleach', 'price' => 10.00],
        4 => ['name' => 'Extra Wash', 'price' => 30.00],
        5 => ['name' => 'Extra Dry', 'price' => 10.00],
    ];

    /**
     * Get the order that owns the item.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the service for this item.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Get addon details with names and prices
    public function getAddonsWithDetailsAttribute()
    {
        if (empty($this->addons)) {
            return [];
        }

        return collect($this->addons)->map(function ($addonId) {
            $addon = self::$addonPrices[$addonId] ?? null;
            if ($addon) {
                return [
                    'id' => $addonId,
                    'name' => $addon['name'],
                    'price' => number_format($addon['price'], 2, '.', '')
                ];
            }
            return null;
        })->filter()->values()->toArray();
    }

    // Calculate add-ons total
    public function getAddonsTotalAttribute()
    {
        if (empty($this->addons)) {
            return 0;
        }

        return collect($this->addons)->sum(function ($addonId) {
            return self::$addonPrices[$addonId]['price'] ?? 0;
        });
    }
}

