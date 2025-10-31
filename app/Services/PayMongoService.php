<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayMongoService
{
    protected $secretKey;
    protected $publicKey;
    protected $apiUrl;

    public function __construct()
    {
        $this->secretKey = config('paymongo.secret_key');
        $this->publicKey = config('paymongo.public_key');
        $this->apiUrl = config('paymongo.api_url');
    }

    /**
     * Create Payment Intent
     */
    public function createPaymentIntent($amount, $description, $metadata = [], $paymentMethods = ['card', 'paymaya'])
    {
        try {
            // PayMongo requires metadata values to be strings only (no nested objects or integers)
            $flatMetadata = [];
            foreach ($metadata as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    // Convert arrays/objects to JSON strings
                    $flatMetadata[$key] = json_encode($value);
                } else {
                    // Convert all values to strings
                    $flatMetadata[$key] = (string)$value;
                }
            }

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->apiUrl}/payment_intents", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amount * 100, // Convert to centavos
                            'currency' => 'PHP',
                            'description' => $description,
                            'statement_descriptor' => 'Laundromat Service',
                            'payment_method_allowed' => $paymentMethods,
                            'metadata' => $flatMetadata,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data'],
                ];
            }

            Log::error('PayMongo Create Payment Intent Failed', [
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['errors'][0]['detail'] ?? 'Failed to create payment intent',
            ];
        } catch (\Exception $e) {
            Log::error('PayMongo Create Payment Intent Exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create Payment Method
     */
    public function createPaymentMethod($type, $details = [])
    {
        try {
            $attributes = [
                'type' => $type,
            ];

            // Add billing details if provided
            if (!empty($details)) {
                $attributes['billing'] = $details;
            }

            $response = Http::withBasicAuth($this->publicKey, '')
                ->post("{$this->apiUrl}/payment_methods", [
                    'data' => [
                        'attributes' => $attributes,
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['errors'][0]['detail'] ?? 'Failed to create payment method',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Attach Payment Method to Intent
     */
    public function attachPaymentMethod($paymentIntentId, $paymentMethodId, $clientKey = null)
    {
        try {
            $auth = $clientKey ? $clientKey : $this->secretKey;
            
            $response = Http::withBasicAuth($auth, '')
                ->post("{$this->apiUrl}/payment_intents/{$paymentIntentId}/attach", [
                    'data' => [
                        'attributes' => [
                            'payment_method' => $paymentMethodId,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['errors'][0]['detail'] ?? 'Failed to attach payment method',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create Source (for GCash, GrabPay, etc.)
     */
    public function createSource($type, $amount, $redirect = [], $metadata = [])
    {
        try {
            // PayMongo requires metadata values to be strings only
            $flatMetadata = [];
            foreach ($metadata as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $flatMetadata[$key] = json_encode($value);
                } else {
                    $flatMetadata[$key] = (string)$value;
                }
            }

            $attributes = [
                'type' => $type,
                'amount' => $amount * 100, // Convert to centavos
                'currency' => 'PHP',
                'redirect' => $redirect,
            ];

            // Only add metadata if not empty
            if (!empty($flatMetadata)) {
                $attributes['metadata'] = $flatMetadata;
            }

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->apiUrl}/sources", [
                    'data' => [
                        'attributes' => $attributes,
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data'],
                ];
            }

            Log::error('PayMongo Create Source Failed', [
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['errors'][0]['detail'] ?? 'Failed to create source',
            ];
        } catch (\Exception $e) {
            Log::error('PayMongo Create Source Exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Create Payment (charge a source)
     */
    public function createPayment($amount, $sourceId, $description, $metadata = [])
    {
        try {
            // PayMongo requires metadata values to be strings only
            $flatMetadata = [];
            foreach ($metadata as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    $flatMetadata[$key] = json_encode($value);
                } else {
                    $flatMetadata[$key] = (string)$value;
                }
            }

            $response = Http::withBasicAuth($this->secretKey, '')
                ->post("{$this->apiUrl}/payments", [
                    'data' => [
                        'attributes' => [
                            'amount' => $amount * 100,
                            'currency' => 'PHP',
                            'description' => $description,
                            'source' => [
                                'id' => $sourceId,
                                'type' => 'source',
                            ],
                            'metadata' => $flatMetadata,
                        ],
                    ],
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json()['errors'][0]['detail'] ?? 'Failed to create payment',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve Payment Intent
     */
    public function retrievePaymentIntent($paymentIntentId, $clientKey = null)
    {
        try {
            $auth = $clientKey ? $clientKey : $this->secretKey;
            
            $response = Http::withBasicAuth($auth, '')
                ->get("{$this->apiUrl}/payment_intents/{$paymentIntentId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data'],
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to retrieve payment intent',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve Payment
     */
    public function retrievePayment($paymentId)
    {
        try {
            $response = Http::withBasicAuth($this->secretKey, '')
                ->get("{$this->apiUrl}/payments/{$paymentId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()['data'],
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to retrieve payment',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify Webhook Signature
     */
    public function verifyWebhookSignature($payload, $signature)
    {
        $webhookSecret = config('paymongo.webhook_secret');
        $computedSignature = hash_hmac('sha256', $payload, $webhookSecret);
        
        return hash_equals($computedSignature, $signature);
    }

    /**
     * Get Available Payment Methods
     */
    public function getAvailablePaymentMethods()
    {
        $methods = config('paymongo.payment_methods');
        
        return array_filter($methods, function ($method) {
            return $method['enabled'];
        });
    }
}
