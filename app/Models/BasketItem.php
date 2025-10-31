<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BasketItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'service_id',
        'quantity',
        'pickup_date',
        'pickup_time',
        'pickup_address',
        'is_rush_service',
        'special_instructions',
        'addons',
    ];

    protected $casts = [
        'pickup_date' => 'date',
        'is_rush_service' => 'boolean',
        'quantity' => 'decimal:2',
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

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Calculate item total
    public function getItemTotalAttribute()
    {
        return $this->service->price * $this->quantity;
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

    // Calculate rush fee (25% of item total)
    public function getRushFeeAttribute()
    {
        return $this->is_rush_service ? $this->item_total * 0.25 : 0;
    }

    // Calculate total (base + addons + rush fee)
    public function getTotalAttribute()
    {
        return $this->item_total + $this->addons_total + $this->rush_fee;
    }

    // Static method to get all available addons
    public static function getAvailableAddons()
    {
        return collect(self::$addonPrices)->map(function ($addon, $id) {
            return [
                'id' => $id,
                'name' => $addon['name'],
                'price' => number_format($addon['price'], 2, '.', '')
            ];
        })->values()->toArray();
    }
}
