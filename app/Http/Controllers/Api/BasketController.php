<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BasketItem;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BasketController extends Controller
{
    /**
     * Get available add-ons (optional endpoint)
     */
    public function getAddons()
    {
        return response()->json([
            'status' => 'success',
            'data' => BasketItem::getAvailableAddons()
        ]);
    }

    /**
     * Get all basket items for authenticated user
     */
    public function index(Request $request)
    {
        $basketItems = BasketItem::with('service')
            ->where('user_id', $request->user()->id)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'service' => [
                        'id' => $item->service->id,
                        'name' => $item->service->name,
                        'price' => $item->service->price,
                        'unit' => $item->service->unit,
                        'icon' => $item->service->icon,
                    ],
                    'quantity' => number_format($item->quantity, 2),
                    'pickup_date' => $item->pickup_date->format('Y-m-d'),
                    'pickup_time' => $item->pickup_time,
                    'pickup_address' => $item->pickup_address,
                    'is_rush_service' => $item->is_rush_service,
                    'special_instructions' => $item->special_instructions,
                    'addons' => $item->addons_with_details,
                    'item_total' => number_format($item->item_total, 2),
                    'addons_total' => number_format($item->addons_total, 2),
                    'rush_fee' => number_format($item->rush_fee, 2),
                    'total' => number_format($item->total, 2),
                ];
            });

        // Calculate basket summary
        $subtotal = $basketItems->sum(function ($item) {
            return (float) str_replace(',', '', $item['item_total']);
        });

        $addonsTotal = $basketItems->sum(function ($item) {
            return (float) str_replace(',', '', $item['addons_total']);
        });

        $rushFee = $basketItems->sum(function ($item) {
            return (float) str_replace(',', '', $item['rush_fee']);
        });

        $total = $subtotal + $addonsTotal + $rushFee;

        return response()->json([
            'status' => 'success',
            'data' => [
                'items' => $basketItems,
                'summary' => [
                    'subtotal' => number_format($subtotal, 2),
                    'addons_total' => number_format($addonsTotal, 2),
                    'rush_fee' => number_format($rushFee, 2),
                    'total' => number_format($total, 2),
                    'item_count' => $basketItems->count(),
                ],
            ],
        ]);
    }

    /**
     * Add item to basket
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|numeric|min:0.1',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|date_format:H:i',
            'pickup_address' => 'required|string',
            'is_rush_service' => 'boolean',
            'special_instructions' => 'nullable|string',
            'addon_ids' => 'nullable|array',
            'addon_ids.*' => 'integer|in:1,2,3,4,5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check if service exists and is active
        $service = Service::where('id', $request->service_id)
            ->where('is_active', true)
            ->first();

        if (!$service) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not available',
            ], 404);
        }

        // Create basket item
        $basketItem = BasketItem::create([
            'user_id' => $request->user()->id,
            'service_id' => $request->service_id,
            'quantity' => $request->quantity,
            'pickup_date' => $request->pickup_date,
            'pickup_time' => $request->pickup_time,
            'pickup_address' => $request->pickup_address,
            'is_rush_service' => $request->is_rush_service ?? false,
            'special_instructions' => $request->special_instructions,
            'addons' => $request->addon_ids ?? [],
        ]);

        $basketItem->load('service');

        return response()->json([
            'status' => 'success',
            'message' => 'Item added to basket',
            'data' => [
                'basket_item' => [
                    'id' => $basketItem->id,
                    'service' => [
                        'id' => $basketItem->service->id,
                        'name' => $basketItem->service->name,
                        'price' => $basketItem->service->price,
                        'unit' => $basketItem->service->unit,
                    ],
                    'quantity' => number_format($basketItem->quantity, 2),
                    'pickup_date' => $basketItem->pickup_date->format('Y-m-d'),
                    'pickup_time' => $basketItem->pickup_time,
                    'pickup_address' => $basketItem->pickup_address,
                    'is_rush_service' => $basketItem->is_rush_service,
                    'special_instructions' => $basketItem->special_instructions,
                    'addons' => $basketItem->addons_with_details,
                    'item_total' => number_format($basketItem->item_total, 2),
                    'addons_total' => number_format($basketItem->addons_total, 2),
                    'rush_fee' => number_format($basketItem->rush_fee, 2),
                    'total' => number_format($basketItem->total, 2),
                ],
            ],
        ], 201);
    }

    /**
     * Update basket item
     */
    public function update(Request $request, $id)
    {
        $basketItem = BasketItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$basketItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Basket item not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|numeric|min:0.1',
            'pickup_date' => 'sometimes|date|after_or_equal:today',
            'pickup_time' => 'sometimes|date_format:H:i',
            'pickup_address' => 'sometimes|string',
            'is_rush_service' => 'sometimes|boolean',
            'special_instructions' => 'nullable|string',
            'addon_ids' => 'nullable|array',
            'addon_ids.*' => 'integer|in:1,2,3,4,5',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Prepare update data
        $updateData = $request->only([
            'quantity',
            'pickup_date',
            'pickup_time',
            'pickup_address',
            'is_rush_service',
            'special_instructions',
        ]);

        // Update addons if provided (replaces existing addons)
        if ($request->has('addon_ids')) {
            $updateData['addons'] = $request->addon_ids ?? [];
        }

        $basketItem->update($updateData);

        $basketItem->load('service');

        return response()->json([
            'status' => 'success',
            'message' => 'Basket item updated',
            'data' => [
                'basket_item' => [
                    'id' => $basketItem->id,
                    'service' => [
                        'id' => $basketItem->service->id,
                        'name' => $basketItem->service->name,
                        'price' => $basketItem->service->price,
                        'unit' => $basketItem->service->unit,
                    ],
                    'quantity' => number_format($basketItem->quantity, 2),
                    'pickup_date' => $basketItem->pickup_date->format('Y-m-d'),
                    'pickup_time' => $basketItem->pickup_time,
                    'pickup_address' => $basketItem->pickup_address,
                    'is_rush_service' => $basketItem->is_rush_service,
                    'special_instructions' => $basketItem->special_instructions,
                    'addons' => $basketItem->addons_with_details,
                    'item_total' => number_format($basketItem->item_total, 2),
                    'addons_total' => number_format($basketItem->addons_total, 2),
                    'rush_fee' => number_format($basketItem->rush_fee, 2),
                    'total' => number_format($basketItem->total, 2),
                ],
            ],
        ]);
    }

    /**
     * Remove item from basket
     */
    public function destroy(Request $request, $id)
    {
        $basketItem = BasketItem::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$basketItem) {
            return response()->json([
                'status' => 'error',
                'message' => 'Basket item not found',
            ], 404);
        }

        $basketItem->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Item removed from basket',
        ]);
    }

    /**
     * Clear all basket items for user
     */
    public function clear(Request $request)
    {
        $deletedCount = BasketItem::where('user_id', $request->user()->id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Basket cleared',
            'data' => [
                'deleted_count' => $deletedCount,
            ],
        ]);
    }

    /**
     * Convert basket to order
     */
    public function checkout(Request $request)
    {
        $basketItems = BasketItem::with('service')
            ->where('user_id', $request->user()->id)
            ->get();

        if ($basketItems->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Basket is empty',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'promo_code' => 'nullable|string',
            'payment_method' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Use the first basket item's details for the order
        $firstItem = $basketItems->first();

        // Prepare services array for order creation
        $services = $basketItems->map(function ($item) {
            return [
                'service_id' => $item->service_id,
                'quantity' => $item->quantity,
                'addons' => $item->addons ?? [],
            ];
        })->toArray();

        // Create the order using OrderController logic
        $orderController = new OrderController();
        $orderRequest = new Request([
            'pickup_date' => $firstItem->pickup_date->format('Y-m-d'),
            'pickup_time' => $firstItem->pickup_time,
            'pickup_address' => $firstItem->pickup_address,
            'is_rush_service' => $basketItems->contains('is_rush_service', true),
            'special_instructions' => $firstItem->special_instructions,
            'promo_code' => $request->promo_code,
            'payment_method' => $request->payment_method ?? 'cash',
            'services' => $services,
        ]);

        $orderRequest->setUserResolver(function () use ($request) {
            return $request->user();
        });

        $orderResponse = $orderController->store($orderRequest);

        // If order created successfully, clear the basket
        if ($orderResponse->status() === 201) {
            BasketItem::where('user_id', $request->user()->id)->delete();
        }

        return $orderResponse;
    }
}
