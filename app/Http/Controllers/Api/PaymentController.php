<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\PayMongoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    protected $payMongoService;

    public function __construct(PayMongoService $payMongoService)
    {
        $this->payMongoService = $payMongoService;
    }

    /**
     * Create Payment Intent (Step 1)
     */
    public function createPaymentIntent(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = Order::with('items.service')
            ->where('id', $request->order_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$order) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found',
            ], 404);
        }

        // Check if order already has a pending payment
        $existingTransaction = PaymentTransaction::where('order_id', $order->id)
            ->where('status', 'pending')
            ->first();

        if ($existingTransaction) {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment intent already exists',
                'data' => [
                    'transaction_id' => $existingTransaction->transaction_id,
                    'payment_intent_id' => $existingTransaction->paymongo_payment_intent_id,
                    'client_key' => $existingTransaction->client_key,
                    'amount' => $existingTransaction->amount,
                ],
            ]);
        }

        // Create payment intent
        $result = $this->payMongoService->createPaymentIntent(
            $order->total_amount,
            "Order #{$order->order_number}",
            [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ]
        );

        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['error'],
            ], 400);
        }

        // Create transaction record
        $transaction = PaymentTransaction::create([
            'transaction_id' => PaymentTransaction::generateTransactionId(),
            'order_id' => $order->id,
            'user_id' => $request->user()->id,
            'amount' => $order->total_amount,
            'currency' => 'PHP',
            'status' => 'pending',
            'payment_method' => $order->payment_method ?? 'pending',
            'paymongo_payment_intent_id' => $result['data']['id'],
            'client_key' => $result['data']['attributes']['client_key'],
            'metadata' => [
                'order_number' => $order->order_number,
            ],
            'response_data' => $result['data'],
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment intent created',
            'data' => [
                'transaction_id' => $transaction->transaction_id,
                'payment_intent_id' => $transaction->paymongo_payment_intent_id,
                'client_key' => $transaction->client_key,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
            ],
        ], 201);
    }

    /**
     * Create Payment Source (Step 2 - for GCash, GrabPay, etc.)
     */
    public function createPaymentSource(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|exists:payment_transactions,transaction_id',
            'payment_method' => 'required|in:gcash,grab_pay,paymaya',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $transaction = PaymentTransaction::where('transaction_id', $request->transaction_id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
            ], 404);
        }

        // Use web routes that will redirect to the app via deep links
        // Append transaction_id and source_id as query params
        $successUrl = config('app.url') . '/payment/success?transaction_id=' . $transaction->transaction_id;
        $failedUrl = config('app.url') . '/payment/failed?transaction_id=' . $transaction->transaction_id;

        // Create source with metadata
        $result = $this->payMongoService->createSource(
            $request->payment_method,
            $transaction->amount,
            [
                'success' => $successUrl,
                'failed' => $failedUrl,
            ],
            [
                'transaction_id' => $transaction->transaction_id,
                'order_id' => $transaction->order_id,
                'order_number' => $transaction->order ? $transaction->order->order_number : '',
            ]
        );

        if (!$result['success']) {
            return response()->json([
                'status' => 'error',
                'message' => $result['error'],
            ], 400);
        }

        // Update transaction
        $transaction->update([
            'payment_method' => $request->payment_method,
            'paymongo_source_id' => $result['data']['id'],
            'checkout_url' => $result['data']['attributes']['redirect']['checkout_url'],
            'status' => 'processing',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment source created',
            'data' => [
                'source_id' => $result['data']['id'],
                'checkout_url' => $result['data']['attributes']['redirect']['checkout_url'],
                'status' => $result['data']['attributes']['status'],
            ],
        ]);
    }

    /**
     * Check Payment Status
     */
    public function checkPaymentStatus(Request $request, $transactionId)
    {
        $transaction = PaymentTransaction::where('transaction_id', $transactionId)
            ->where('user_id', $request->user()->id)
            ->with('order')
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
            ], 404);
        }

        // If payment is already successful, return status
        if ($transaction->isPaid()) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'payment_status' => 'paid',
                    'amount' => $transaction->amount,
                    'paid_at' => $transaction->paid_at->format('Y-m-d H:i:s'),
                    'order' => [
                        'id' => $transaction->order->id,
                        'order_number' => $transaction->order->order_number,
                        'status' => $transaction->order->status,
                    ],
                ],
            ]);
        }

        // Check with PayMongo
        if ($transaction->paymongo_payment_intent_id) {
            $result = $this->payMongoService->retrievePaymentIntent(
                $transaction->paymongo_payment_intent_id
            );

            if ($result['success']) {
                $paymentStatus = $result['data']['attributes']['status'];
                
                // Update transaction status
                if ($paymentStatus === 'succeeded') {
                    $this->markTransactionAsPaid($transaction);
                } elseif (in_array($paymentStatus, ['failed', 'cancelled'])) {
                    $transaction->update(['status' => 'failed']);
                }

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'transaction_id' => $transaction->transaction_id,
                        'payment_status' => $transaction->status,
                        'amount' => $transaction->amount,
                        'paymongo_status' => $paymentStatus,
                    ],
                ]);
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'transaction_id' => $transaction->transaction_id,
                'payment_status' => $transaction->status,
                'amount' => $transaction->amount,
            ],
        ]);
    }

    /**
     * Process Payment Source (Manual trigger after redirect)
     */
    public function processPaymentSource(Request $request, $transactionId)
    {
        $transaction = PaymentTransaction::with('order')
            ->where('transaction_id', $transactionId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$transaction) {
            return response()->json([
                'status' => 'error',
                'message' => 'Transaction not found',
            ], 404);
        }

        // If already paid, return success
        if (in_array($transaction->status, ['paid', 'succeeded'])) {
            return response()->json([
                'status' => 'success',
                'message' => 'Payment already processed',
                'data' => [
                    'transaction_id' => $transaction->transaction_id,
                    'payment_status' => $transaction->status,
                    'amount' => $transaction->amount,
                ],
            ]);
        }

        // Check if we have a source_id
        if (!$transaction->paymongo_source_id) {
            return response()->json([
                'status' => 'error',
                'message' => 'No payment source found for this transaction',
            ], 400);
        }

        Log::info('Manual payment processing triggered', [
            'transaction_id' => $transactionId,
            'source_id' => $transaction->paymongo_source_id,
        ]);

        // Retrieve source status from PayMongo
        try {
            $response = Http::withBasicAuth(config('paymongo.secret_key'), '')
                ->get(config('paymongo.api_url') . '/sources/' . $transaction->paymongo_source_id);

            if ($response->successful()) {
                $sourceData = $response->json()['data'];
                $sourceStatus = $sourceData['attributes']['status'];

                Log::info('Source status retrieved', [
                    'source_id' => $transaction->paymongo_source_id,
                    'status' => $sourceStatus,
                ]);

                // If source is chargeable, create payment
                if ($sourceStatus === 'chargeable') {
                    $description = $transaction->order 
                        ? "Order #{$transaction->order->order_number}" 
                        : "Order #{$transaction->order_id}";

                    $result = $this->payMongoService->createPayment(
                        $transaction->amount,
                        $transaction->paymongo_source_id,
                        $description,
                        [
                            'transaction_id' => (string)$transaction->transaction_id,
                            'order_id' => (string)$transaction->order_id,
                        ]
                    );

                    if ($result['success']) {
                        $paymentData = $result['data'];
                        
                        $transaction->update([
                            'paymongo_payment_id' => $paymentData['id'],
                            'status' => $paymentData['attributes']['status'],
                            'response_data' => json_encode($paymentData),
                        ]);

                        // If payment is succeeded, mark as paid
                        if ($paymentData['attributes']['status'] === 'paid') {
                            $this->markTransactionAsPaid($transaction);
                        }

                        return response()->json([
                            'status' => 'success',
                            'message' => 'Payment processed successfully',
                            'data' => [
                                'transaction_id' => $transaction->transaction_id,
                                'payment_id' => $paymentData['id'],
                                'payment_status' => $paymentData['attributes']['status'],
                                'amount' => $transaction->amount,
                            ],
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'Failed to create payment: ' . $result['error'],
                        ], 400);
                    }
                } elseif ($sourceStatus === 'paid') {
                    // Source already paid, mark transaction as paid
                    $this->markTransactionAsPaid($transaction);
                    
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Payment already completed',
                        'data' => [
                            'transaction_id' => $transaction->transaction_id,
                            'payment_status' => 'paid',
                            'amount' => $transaction->amount,
                        ],
                    ]);
                } else {
                    return response()->json([
                        'status' => 'pending',
                        'message' => 'Payment is still processing',
                        'data' => [
                            'transaction_id' => $transaction->transaction_id,
                            'source_status' => $sourceStatus,
                            'amount' => $transaction->amount,
                        ],
                    ]);
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to retrieve source status from PayMongo',
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Error processing payment source', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'An error occurred while processing payment',
            ], 500);
        }
    }

    /**
     * Handle PayMongo Webhook
     */
    public function handleWebhook(Request $request)
    {
        // Log the incoming webhook
        Log::info('PayMongo Webhook Received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        // Verify webhook signature only if provided (PayMongo dashboard webhooks don't include signature in test mode)
        $signature = $request->header('Paymongo-Signature');
        $payload = $request->getContent();

        if ($signature) {
            if (!$this->payMongoService->verifyWebhookSignature($payload, $signature)) {
                Log::warning('PayMongo webhook signature verification failed', [
                    'signature' => $signature,
                ]);
                return response()->json(['message' => 'Invalid signature'], 400);
            }
            Log::info('Webhook signature verified successfully');
        } else {
            Log::info('No signature provided - processing webhook without verification (test mode)');
        }

        $event = $request->input('data');
        $eventType = $event['attributes']['type'] ?? null;

        if (!$eventType) {
            Log::error('No event type found in webhook');
            return response()->json(['message' => 'Invalid webhook data'], 400);
        }

        Log::info('PayMongo Webhook Received', [
            'type' => $eventType,
            'data' => $event,
        ]);

        try {
            switch ($eventType) {
                case 'source.chargeable':
                    $this->handleSourceChargeable($event);
                    break;

                case 'payment.paid':
                    $this->handlePaymentPaid($event);
                    break;

                case 'payment.failed':
                    $this->handlePaymentFailed($event);
                    break;
            }

            return response()->json(['message' => 'Webhook processed'], 200);
        } catch (\Exception $e) {
            Log::error('PayMongo Webhook Error', [
                'error' => $e->getMessage(),
                'event' => $event,
            ]);

            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Get Available Payment Methods
     */
    public function getPaymentMethods()
    {
        $methods = $this->payMongoService->getAvailablePaymentMethods();

        return response()->json([
            'status' => 'success',
            'data' => [
                'payment_methods' => array_values($methods),
            ],
        ]);
    }

    /**
     * Handle source.chargeable webhook event
     */
    protected function handleSourceChargeable($event)
    {
        $sourceId = $event['attributes']['data']['id'];
        $sourceData = $event['attributes']['data']['attributes'];
        $metadata = $sourceData['metadata'] ?? [];
        
        Log::info('Processing source.chargeable', [
            'source_id' => $sourceId,
            'metadata' => $metadata,
        ]);

        // Try to find transaction by source_id first
        $transaction = PaymentTransaction::with('order')->where('paymongo_source_id', $sourceId)->first();

        // If not found, try to find by transaction_id in metadata
        if (!$transaction && isset($metadata['transaction_id'])) {
            $transaction = PaymentTransaction::with('order')->where('transaction_id', $metadata['transaction_id'])->first();
            
            // Update the transaction with the source_id
            if ($transaction) {
                $transaction->update(['paymongo_source_id' => $sourceId]);
            }
        }

        if (!$transaction) {
            Log::warning('Transaction not found for source', [
                'source_id' => $sourceId,
                'metadata' => $metadata,
            ]);
            return;
        }

        // Create payment from source
        $description = $transaction->order 
            ? "Order #{$transaction->order->order_number}" 
            : "Order #{$transaction->order_id}";

        $result = $this->payMongoService->createPayment(
            $transaction->amount,
            $sourceId,
            $description,
            [
                'transaction_id' => (string)$transaction->transaction_id,
                'order_id' => (string)$transaction->order_id,
            ]
        );

        if ($result['success']) {
            $transaction->update([
                'paymongo_payment_id' => $result['data']['id'],
                'status' => 'processing',
                'response_data' => json_encode($result['data']),
            ]);

            Log::info('Payment created from source', [
                'transaction_id' => $transaction->transaction_id,
                'payment_id' => $result['data']['id'],
            ]);
        } else {
            Log::error('Failed to create payment from source', [
                'transaction_id' => $transaction->transaction_id,
                'error' => $result['error'],
            ]);
        }
    }

    /**
     * Handle payment.paid webhook event
     */
    protected function handlePaymentPaid($event)
    {
        $paymentId = $event['attributes']['data']['id'];
        
        $transaction = PaymentTransaction::where('paymongo_payment_id', $paymentId)
            ->orWhere('paymongo_payment_intent_id', $paymentId)
            ->first();

        if (!$transaction) {
            Log::warning('Transaction not found for payment', ['payment_id' => $paymentId]);
            return;
        }

        $this->markTransactionAsPaid($transaction);
    }

    /**
     * Handle payment.failed webhook event
     */
    protected function handlePaymentFailed($event)
    {
        $paymentId = $event['attributes']['data']['id'];
        
        $transaction = PaymentTransaction::where('paymongo_payment_id', $paymentId)
            ->orWhere('paymongo_payment_intent_id', $paymentId)
            ->first();

        if (!$transaction) {
            return;
        }

        $transaction->update(['status' => 'failed']);

        if ($transaction->order) {
            $transaction->order->update([
                'payment_status' => 'unpaid',
                'status' => 'cancelled',
            ]);
        }
    }

    /**
     * Mark transaction as paid and update order
     */
    protected function markTransactionAsPaid($transaction)
    {
        DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            if ($transaction->order) {
                $transaction->order->update([
                    'payment_status' => 'paid',
                    'paid_at' => now(),
                    'status' => 'pending',
                ]);
            }
        });
    }

    /**
     * Validate URL (accepts both standard URLs and custom schemes)
     */
    protected function isValidUrl($url)
    {
        // Accept custom schemes like laundryapp://
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:\/\/.+/', $url)) {
            return true;
        }

        // Fallback to standard URL validation
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }
}
