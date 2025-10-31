<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send order confirmation notification to user
     * 
     * @param Order $order
     * @return void
     */
    public function sendOrderConfirmation(Order $order)
    {
        // Ensure relationships are loaded
        $order->load(['user', 'items.service']);
        
        $user = $order->user;

        // Send email notification if user allows it
        if ($user->allow_email_notifications) {
            $this->sendOrderConfirmationEmail($order);
        }

        // Send SMS notification if user allows it
        if ($user->allow_sms_notifications) {
            $this->sendOrderConfirmationSMS($order);
        }
    }

    /**
     * Send order confirmation email
     * 
     * @param Order $order
     * @return void
     */
    private function sendOrderConfirmationEmail(Order $order)
    {
        try {
            Mail::send('emails.order-confirmation', ['order' => $order], function ($message) use ($order) {
                $message->to($order->user->email, $order->user->name)
                    ->subject('Order Confirmation - ' . $order->order_number);
            });

            Log::info("Order confirmation email sent to {$order->user->email} for order {$order->order_number}");
        } catch (\Exception $e) {
            Log::error("Failed to send order confirmation email: " . $e->getMessage());
        }
    }

    /**
     * Send order confirmation SMS via Semaphore
     * 
     * @param Order $order
     * @return void
     */
    private function sendOrderConfirmationSMS(Order $order)
    {
        try {
            $apiKey = config('services.semaphore.api_key');
            $senderName = config('services.semaphore.sender_name', 'Laundry');

            if (empty($apiKey)) {
                Log::warning('Semaphore API key not configured');
                return;
            }

            // Clean phone number (remove spaces, dashes, etc.)
            $phoneNumber = preg_replace('/[^0-9+]/', '', $order->user->mobile_number);

            // Prepare SMS message
            $message = $this->formatOrderConfirmationSMS($order);

            // Send SMS via Semaphore API
            $response = Http::asForm()->post('https://api.semaphore.co/api/v4/messages', [
                'apikey' => $apiKey,
                'number' => $phoneNumber,
                'message' => $message,
                'sendername' => $senderName,
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                Log::info("Order confirmation SMS sent to {$phoneNumber} for order {$order->order_number}", [
                    'response' => $responseData,
                ]);
            } else {
                Log::error("Failed to send SMS via Semaphore", [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Exception while sending SMS: " . $e->getMessage());
        }
    }

    /**
     * Format SMS message for order confirmation
     * 
     * @param Order $order
     * @return string
     */
    private function formatOrderConfirmationSMS(Order $order)
    {
        $message = "Hi {$order->user->name},\n\n";
        $message .= "Your Laundry Order {$order->order_number} has been confirmed.\n\n";
        $message .= "Pickup: {$order->pickup_date->format('M d, Y')} at {$order->pickup_time->format('h:i A')} - {$order->pickup_address}\n";
        $message .= "Total: PHP " . number_format($order->total_amount, 2) . "\n\n";
        $message .= "Thank you for choosing Crystal Bubble Laundry Shop.";

        return $message;
    }

    /**
     * Send order status update notification
     * 
     * @param Order $order
     * @param string $oldStatus
     * @param string $newStatus
     * @return void
     */
    public function sendOrderStatusUpdate(Order $order, string $oldStatus, string $newStatus)
    {
        $user = $order->user;

        // Send email notification if user allows it
        if ($user->allow_email_notifications) {
            $this->sendStatusUpdateEmail($order, $newStatus);
        }

        // Send SMS notification if user allows it
        if ($user->allow_sms_notifications) {
            $this->sendStatusUpdateSMS($order, $newStatus);
        }
    }

    /**
     * Send status update email
     * 
     * @param Order $order
     * @param string $status
     * @return void
     */
    private function sendStatusUpdateEmail(Order $order, string $status)
    {
        try {
            Mail::send('emails.order-status-update', [
                'order' => $order,
                'status' => $status
            ], function ($message) use ($order) {
                $message->to($order->user->email, $order->user->name)
                    ->subject('Order Status Update - ' . $order->order_number);
            });

            Log::info("Status update email sent for order {$order->order_number}");
        } catch (\Exception $e) {
            Log::error("Failed to send status update email: " . $e->getMessage());
        }
    }

    /**
     * Send status update SMS
     * 
     * @param Order $order
     * @param string $status
     * @return void
     */
    private function sendStatusUpdateSMS(Order $order, string $status)
    {
        try {
            $apiKey = config('services.semaphore.api_key');
            $apiUrl = config('services.semaphore.api_url');
            $senderName = config('services.semaphore.sender_name');

            if (empty($apiKey)) {
                Log::warning('Semaphore API key not configured');
                return;
            }

            $phoneNumber = preg_replace('/[^0-9+]/', '', $order->user->mobile_number);
            $message = $this->formatStatusUpdateSMS($order, $status);

            $response = Http::post("{$apiUrl}/messages", [
                'apikey' => $apiKey,
                'number' => $phoneNumber,
                'message' => $message,
                'sendername' => $senderName,
            ]);

            if ($response->successful()) {
                Log::info("Status update SMS sent for order {$order->order_number}");
            } else {
                Log::error("Failed to send status update SMS", [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Exception while sending status update SMS: " . $e->getMessage());
        }
    }

    /**
     * Format SMS message for status update
     * 
     * @param Order $order
     * @param string $status
     * @return string
     */
    private function formatStatusUpdateSMS(Order $order, string $status)
    {
        $statusMessages = [
            'picked_up' => 'Your laundry has been picked up and is on its way to our facility.',
            'processing' => 'Your laundry is now being processed.',
            'ready' => 'Good news! Your laundry is ready for delivery.',
            'out_for_delivery' => 'Your laundry is out for delivery and will arrive soon.',
            'completed' => 'Your order has been completed. Thank you!',
            'cancelled' => 'Your order has been cancelled.',
        ];

        $statusText = $statusMessages[$status] ?? "Your order status has been updated to: {$status}";

        $message = "Order Update: {$order->order_number}\n\n";
        $message .= $statusText . "\n\n";
        $message .= "Thank you for choosing us!";

        return $message;
    }
}
