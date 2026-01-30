<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service to send messages via the WhatsApp Business API (Facebook Graph API).
 *
 * Expected configuration keys (config/services.php):
 *  'whatsapp_business' => [
 *      'base_url' => 'https://graph.facebook.com/v17.0',
 *      'phone_number_id' => env('WHATSAPP_BUSINESS_PHONE_NUMBER_ID'),
 *      'token' => env('WHATSAPP_BUSINESS_TOKEN'),
 *  ]
 */
class WhatsAppService
{
    protected string $baseUrl;
    protected ?string $phoneNumberId;
    protected ?string $token;

    public function __construct()
    {
        $this->token = config('services.whatsapp_business.token', env('WHATSAPP_BUSINESS_TOKEN'));
        $this->phoneNumberId = config('services.whatsapp_business.phone_number_id', env('WHATSAPP_BUSINESS_PHONE_NUMBER_ID'));
        $this->baseUrl = rtrim(config('services.whatsapp_business.base_url', env('WHATSAPP_BUSINESS_URL', 'https://graph.facebook.com/v17.0')), '/');
    }

    protected function endpoint(string $path = ''): string
    {
        if (empty($this->phoneNumberId)) {
            throw new \RuntimeException('WhatsApp phone number id is not configured (services.whatsapp_business.phone_number_id).');
        }

        $path = ltrim($path, '/');
        return "{$this->baseUrl}/{$this->phoneNumberId}/{$path}";
    }

    /**
     * Send a plain text message.
     *
     * @param string $to  Phone number in international format without '+' (e.g. '23354xxxxxxx')
     * @param string $message
     * @return array API response decoded as array
     * @throws \Throwable
     */
    public function sendText(string $to, string $message): array
    {
        $url = $this->endpoint('messages');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'text',
            'text' => [
                'body' => $message,
            ],
        ];

        return $this->post($url, $payload);
    }

    /**
     * Send a template message.
     *
     * @param string $to
     * @param string $templateName
     * @param string $languageCode
     * @param array $components
     * @return array
     * @throws \Throwable
     */
    public function sendTemplate(string $to, string $templateName, string $languageCode = 'en_US', array $components = []): array
    {
        $url = $this->endpoint('messages');

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $to,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => ['code' => $languageCode],
                'components' => $components,
            ],
        ];

        return $this->post($url, $payload);
    }

    /**
     * Internal POST helper with token and error handling.
     *
     * @param string $url
     * @param array $payload
     * @return array
     * @throws \RuntimeException
     */
    protected function post(string $url, array $payload): array
    {
        if (empty($this->token)) {
            throw new \RuntimeException('WhatsApp Business API token is not configured (services.whatsapp_business.token).');
        }

        try {
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withToken($this->token)
                ->acceptJson()
                ->post($url, $payload);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('WhatsApp API error', [
                'url' => $url,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            throw new \RuntimeException('WhatsApp API request failed with status ' . $response->status());
        } catch (\Throwable $e) {
            Log::error('WhatsApp send failed', [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        }
    }
}
