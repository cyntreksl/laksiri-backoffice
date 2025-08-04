<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    protected string $apiUrl;

    protected string $phoneNumberId;

    protected string $accessToken;

    protected string $apiVersion;

    protected string $baseUrl;

    public function __construct()
    {
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->accessToken = config('services.whatsapp.access_token');
        $this->apiVersion = config('services.whatsapp.api_version', 'v21.0');
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
        $this->apiUrl = "{$this->baseUrl}/{$this->phoneNumberId}/messages";
    }

    /**
     * Check if WhatsApp service is enabled
     */
    public function isWhatsAppEnabled(): bool
    {
        return config('services.whatsapp.enabled', false);
    }

    /**
     * Check if WhatsApp is in test mode
     */
    public function isWhatsAppTestMode(): bool
    {
        return ! empty(config('services.whatsapp.test_receiver_phone_number'));
    }

    /**
     * Send a message using a template
     */
    public function sendMessageByTemplate(array $body): mixed
    {
        if (! $this->isWhatsAppEnabled()) {
            return false;
        }

        try {
            if (! $this->accessToken || ! $this->phoneNumberId) {
                throw new \Exception('WhatsApp API credentials not configured');
            }

            if ($this->isWhatsAppTestMode()) {
                $body['to'] = config('services.whatsapp.test_receiver_phone_number');
            }

            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl, $body);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('WhatsApp template message sent successfully', [
                    'to' => $body['to'] ?? 'unknown',
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'response' => $responseData,
                ]);

                return $responseData;
            } else {
                $error = $response->json();

                Log::error('WhatsApp API Error', [
                    'error' => $error,
                    'status_code' => $response->status(),
                    'message' => $response->body(),
                    'to' => $body['to'] ?? 'unknown',
                ]);

                return $error;
            }
        } catch (\Throwable $e) {
            Log::error('WhatsApp template message exception', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
                'to' => $body['to'] ?? 'unknown',
                'trace' => $e->getTraceAsString(),
            ]);

            return $e->getMessage();
        }
    }

    /**
     * Send a simple text message via WhatsApp Business API
     */
    public function sendMessage(string $to, string $message): array
    {
        if (! $this->isWhatsAppEnabled()) {
            return [
                'success' => false,
                'error' => 'WhatsApp service is disabled',
            ];
        }

        try {
            if (! $this->accessToken || ! $this->phoneNumberId) {
                throw new \Exception('WhatsApp API credentials not configured');
            }

            // Apply test mode if enabled
            if ($this->isWhatsAppTestMode()) {
                $to = config('services.whatsapp.test_receiver_phone_number');
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->accessToken,
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('WhatsApp text message sent successfully', [
                    'to' => $to,
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'response' => $responseData,
                ]);

                return [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'response' => $responseData,
                ];
            } else {
                $errorData = $response->json();

                Log::error('WhatsApp text message API error', [
                    'status' => $response->status(),
                    'error' => $errorData,
                    'to' => $to,
                ]);

                return [
                    'success' => false,
                    'error' => $errorData['error']['message'] ?? 'Unknown API error',
                    'error_code' => $errorData['error']['code'] ?? null,
                    'response' => $errorData,
                ];
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp text message service error: '.$e->getMessage(), [
                'to' => $to,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send media message (image, document, etc.)
     */
    public function sendMediaMessage(string $to, string $type, string $mediaId, ?string $caption = null): array
    {
        if (! $this->isWhatsAppEnabled()) {
            return [
                'success' => false,
                'error' => 'WhatsApp service is disabled',
            ];
        }

        try {
            if (! $this->accessToken || ! $this->phoneNumberId) {
                throw new \Exception('WhatsApp API credentials not configured');
            }

            // Apply test mode if enabled
            if ($this->isWhatsAppTestMode()) {
                $to = config('services.whatsapp.test_receiver_phone_number');
            }

            $payload = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => $type,
                $type => [
                    'id' => $mediaId,
                ],
            ];

            if ($caption && in_array($type, ['image', 'document', 'video'])) {
                $payload[$type]['caption'] = $caption;
            }

            $response = Http::withToken($this->accessToken)
                ->post($this->apiUrl, $payload);

            if ($response->successful()) {
                $responseData = $response->json();

                Log::info('WhatsApp media message sent successfully', [
                    'to' => $to,
                    'type' => $type,
                    'media_id' => $mediaId,
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'response' => $responseData,
                ]);

                return [
                    'success' => true,
                    'message_id' => $responseData['messages'][0]['id'] ?? null,
                    'response' => $responseData,
                ];
            } else {
                $errorData = $response->json();

                Log::error('WhatsApp media message API error', [
                    'status' => $response->status(),
                    'error' => $errorData,
                    'to' => $to,
                    'type' => $type,
                    'media_id' => $mediaId,
                ]);

                return [
                    'success' => false,
                    'error' => $errorData['error']['message'] ?? 'Unknown API error',
                    'error_code' => $errorData['error']['code'] ?? null,
                    'response' => $errorData,
                ];
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp media message service error: '.$e->getMessage(), [
                'to' => $to,
                'type' => $type,
                'media_id' => $mediaId,
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
