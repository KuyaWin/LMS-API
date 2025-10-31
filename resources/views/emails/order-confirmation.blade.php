<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 0 0 5px 5px;
        }
        .order-details {
            background-color: white;
            padding: 15px;
            margin: 15px 0;
            border-radius: 5px;
            border: 1px solid #e0e0e0;
        }
        .order-number {
            font-size: 24px;
            font-weight: bold;
            color: #4CAF50;
            margin-bottom: 10px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .detail-label {
            font-weight: bold;
            color: #666;
        }
        .detail-value {
            color: #333;
        }
        .services-list {
            margin: 15px 0;
        }
        .service-item {
            background-color: #f5f5f5;
            padding: 10px;
            margin: 8px 0;
            border-radius: 3px;
        }
        .addon-item {
            font-size: 14px;
            color: #666;
            padding-left: 20px;
            margin-top: 5px;
        }
        .total-amount {
            font-size: 28px;
            font-weight: bold;
            color: #4CAF50;
            text-align: right;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 2px solid #4CAF50;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Order Confirmation</h1>
    </div>
    
    <div class="content">
        <p>Hello {{ $order->user->name }},</p>
        <p>Thank you for your order! We've received your payment and your laundry order has been confirmed.</p>
        
        <div class="order-details">
            <div class="order-number">Order #{{ $order->order_number }}</div>
            
            <div class="detail-row">
                <span class="detail-label">Pickup Date:</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($order->pickup_date)->format('M d, Y') }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Pickup Time:</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($order->pickup_time)->format('h:i A') }}</span>
            </div>
            
            @if($order->rush)
            <div class="detail-row">
                <span class="detail-label">Rush Service:</span>
                <span class="detail-value" style="color: #ff9800; font-weight: bold;">YES (+20%)</span>
            </div>
            @endif
            
            <div class="detail-row">
                <span class="detail-label">Payment Status:</span>
                <span class="detail-value" style="color: #4CAF50; font-weight: bold;">{{ strtoupper($order->payment_status) }}</span>
            </div>
        </div>

        <div class="services-list">
            <h3>Services Ordered:</h3>
            @foreach($order->items as $item)
                <div class="service-item">
                    <div>
                        <strong>{{ $item->service->service_name }}</strong> 
                        (x{{ $item->quantity }})
                        <span style="float: right;">₱{{ number_format($item->item_total, 2) }}</span>
                    </div>
                    
                    @if($item->addons && count($item->addons) > 0)
                        <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed #ddd;">
                            <small style="color: #666;">Add-ons:</small>
                            @foreach($item->addons_with_details as $addon)
                                <div class="addon-item">
                                    • {{ $addon['name'] }} 
                                    <span style="float: right;">₱{{ number_format($addon['price'], 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div style="background-color: white; padding: 15px; border-radius: 5px; margin-top: 20px;">
            <div class="detail-row">
                <span class="detail-label">Subtotal:</span>
                <span class="detail-value">₱{{ number_format($order->subtotal, 2) }}</span>
            </div>
            
            @if($order->addons_total > 0)
            <div class="detail-row">
                <span class="detail-label">Add-ons Total:</span>
                <span class="detail-value">₱{{ number_format($order->addons_total, 2) }}</span>
            </div>
            @endif
            
            @if($order->rush)
            <div class="detail-row">
                <span class="detail-label">Rush Fee (25%):</span>
                <span class="detail-value">₱{{ number_format($order->rush_fee, 2) }}</span>
            </div>
            @endif
            
            <div class="total-amount">
                Total: ₱{{ number_format($order->total_amount, 2) }}
            </div>
        </div>

        <p style="margin-top: 25px;">We'll notify you when your laundry is ready for pickup. If you have any questions, please don't hesitate to contact us.</p>
    </div>

    <div class="footer">
        <p>Thank you for choosing our laundry service!</p>
        <p style="font-size: 12px; color: #999;">This is an automated email. Please do not reply.</p>
    </div>
</body>
</html>
