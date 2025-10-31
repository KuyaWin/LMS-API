<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Service;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Create a new order (Book a service)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|date_format:H:i',
            'pickup_address' => 'required|string',
            'is_rush_service' => 'boolean',
            'special_instructions' => 'nullable|string',
            'promo_code' => 'nullable|string',
            'services' => 'required|array|min:1',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.quantity' => 'required|numeric|min:0.1',
            'services.*.addons' => 'nullable|array',
            'services.*.addons.*' => 'integer|in:1,2,3,4,5',
        ]);

        DB::beginTransaction();

        try {
            // Calculate order totals
            $subtotal = 0;
            $addonsTotal = 0;
            $orderItems = [];

            // Addon prices
            $addonPrices = [
                1 => 10.00, // Extra Soap
                2 => 10.00, // Extra Fabric Conditioner
                3 => 10.00, // Bleach
                4 => 30.00, // Extra Wash
                5 => 10.00, // Extra Dry
            ];

            foreach ($request->services as $serviceData) {
                $service = Service::findOrFail($serviceData['service_id']);
                $quantity = $serviceData['quantity'];
                $itemTotal = $service->price * $quantity;
                
                // Calculate addons total for this item
                $itemAddonsTotal = 0;
                if (!empty($serviceData['addons'])) {
                    foreach ($serviceData['addons'] as $addonId) {
                        $itemAddonsTotal += $addonPrices[$addonId] ?? 0;
                    }
                }
                
                $orderItems[] = [
                    'service_id' => $service->id,
                    'quantity' => $quantity,
                    'unit_price' => $service->price,
                    'total_price' => $itemTotal,
                    'addons' => $serviceData['addons'] ?? [],
                ];

                $subtotal += $itemTotal;
                $addonsTotal += $itemAddonsTotal;
            }

            // Calculate rush fee (25% of subtotal if rush service)
            $rushFee = $request->is_rush_service ? $subtotal * 0.25 : 0;

            // TODO: Apply promo code discount logic here
            $discountAmount = 0;

            $totalAmount = $subtotal + $addonsTotal + $rushFee - $discountAmount;

            // Create order
            $order = Order::create([
                'order_number' => Order::generateOrderNumber(),
                'user_id' => $request->user()->id,
                'pickup_date' => $request->pickup_date,
                'pickup_time' => $request->pickup_time,
                'pickup_address' => $request->pickup_address,
                'is_rush_service' => $request->is_rush_service ?? false,
                'special_instructions' => $request->special_instructions,
                'promo_code' => $request->promo_code,
                'discount_amount' => $discountAmount,
                'subtotal' => $subtotal,
                'rush_fee' => $rushFee,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'paid', // Assuming payment is made upon booking
                'payment_method' => $request->payment_method ?? 'online',
                'paid_at' => now(),
            ]);

            // Create order items
            foreach ($orderItems as $itemData) {
                $order->items()->create($itemData);
            }

            DB::commit();

            // Load relationships for response
            $order->load(['items.service', 'user']);

            // Add loyalty points for paid order (1 point per PHP 10 spent)
            $pointsEarned = $order->user->addLoyaltyPoints($order->total_amount);

            // Send notifications based on user preferences
            try {
                $notificationService = new NotificationService();
                $notificationService->sendOrderConfirmation($order);
            } catch (\Exception $e) {
                // Log error but don't fail the order creation
                \Log::error('Failed to send order confirmation notification: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Order created successfully',
                'data' => [
                    'order' => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'pickup_date' => $order->pickup_date->format('M d, Y'),
                        'pickup_time' => $order->pickup_time->format('h:i A'),
                        'pickup_address' => $order->pickup_address,
                        'is_rush_service' => $order->is_rush_service,
                        'special_instructions' => $order->special_instructions,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'services' => $order->items->map(function ($item) {
                            return [
                                'name' => $item->service->name,
                                'quantity' => $item->quantity,
                                'unit' => $item->service->unit,
                                'unit_price' => $item->unit_price,
                                'total_price' => $item->total_price,
                                'addons' => $item->addons_with_details,
                            ];
                        }),
                        'subtotal' => $order->subtotal,
                        'addons_total' => number_format($addonsTotal, 2, '.', ''),
                        'rush_fee' => $order->rush_fee,
                        'discount_amount' => $order->discount_amount,
                        'total_amount' => $order->total_amount,
                        'created_at' => $order->created_at->format('M d, Y h:i A'),
                    ]
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create order: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get latest 5 orders for authenticated user
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $orders = Order::with(['items.service'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'pickup_date' => $order->pickup_date->format('M d, Y'),
                        'pickup_time' => $order->pickup_time->format('h:i A'),
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'is_rush_service' => $order->is_rush_service,
                        'services' => $order->items->map(function ($item) {
                            return [
                                'name' => $item->service->name,
                                'quantity' => $item->quantity . ' ' . $item->service->unit,
                            ];
                        }),
                        'total_amount' => $order->total_amount,
                        'created_at' => $order->created_at->format('M d, Y'),
                    ];
                })
            ]
        ], 200);
    }

    /**
     * Get a specific order by ID
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, $id)
    {
        $order = Order::with(['items.service', 'user'])
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'customer' => [
                        'name' => $order->user->name,
                        'email' => $order->user->email,
                        'mobile_number' => $order->user->mobile_number,
                    ],
                    'pickup_date' => $order->pickup_date->format('M d, Y'),
                    'pickup_time' => $order->pickup_time->format('h:i A'),
                    'pickup_address' => $order->pickup_address,
                    'is_rush_service' => $order->is_rush_service,
                    'special_instructions' => $order->special_instructions,
                    'promo_code' => $order->promo_code,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'payment_method' => $order->payment_method,
                    'services' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'service_name' => $item->service->name,
                            'quantity' => $item->quantity,
                            'unit' => $item->service->unit,
                            'unit_price' => $item->unit_price,
                            'total_price' => $item->total_price,
                            'addons' => $item->addons_with_details,
                        ];
                    }),
                    'subtotal' => $order->subtotal,
                    'addons_total' => number_format($order->items->sum('addons_total'), 2, '.', ''),
                    'rush_fee' => $order->rush_fee,
                    'discount_amount' => $order->discount_amount,
                    'total_amount' => $order->total_amount,
                    'placed_on' => $order->created_at->format('M d, Y h:i A'),
                    'paid_at' => $order->paid_at ? $order->paid_at->format('M d, Y h:i A') : null,
                ]
            ]
        ], 200);
    }

    /**
     * Update order status (for admin)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_transit,picked_up,processing,ready,out_for_delivery,completed,cancelled'
        ]);

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found'
            ], 404);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated successfully',
            'data' => [
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                ]
            ]
        ], 200);
    }
}

