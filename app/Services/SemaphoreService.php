<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SemaphoreService
{
    private $apiKey;
    private $apiUrl = 'https://api.semaphore.co/api/v4/messages';
    private $senderName;

    public function __construct()
    {
        $this->apiKey = config('services.semaphore.api_key');
        $this->senderName = config('services.semaphore.sender_name', 'SEMAPHORE');
    }

    /**
     * Send SMS via Semaphore API
     *
     * @param string|array $number Phone number(s) - can be string or array
     * @param string $message Message content
     * @param string|null $senderName Optional sender name override
     * @return array Response from Semaphore API
     */
    public function sendSMS($number, string $message, ?string $senderName = null): array
    {
        try {
            // Convert array to comma-separated string
            if (is_array($number)) {
                $number = implode(',', $number);
            }

            // Clean phone number (remove spaces, dashes, etc.)
            $number = preg_replace('/[^0-9,+]/', '', $number);

            $payload = [
                'apikey' => $this->apiKey,
                'number' => $number,
                'message' => $message,
                'sendername' => $senderName ?? $this->senderName,
            ];

            $response = Http::asForm()->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::info('SMS sent successfully', [
                    'recipient' => $number,
                    'message_id' => $data[0]['message_id'] ?? null,
                    'status' => $data[0]['status'] ?? null,
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                ];
            } else {
                Log::error('SMS send failed', [
                    'recipient' => $number,
                    'status_code' => $response->status(),
                    'response' => $response->body(),
                ]);

                return [
                    'success' => false,
                    'error' => 'SMS send failed: ' . $response->body(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('SMS send exception', [
                'recipient' => $number,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check account balance
     *
     * @return array
     */
    public function getBalance(): array
    {
        try {
            $response = Http::get('https://api.semaphore.co/api/v4/account', [
                'apikey' => $this->apiKey,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to fetch balance',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
