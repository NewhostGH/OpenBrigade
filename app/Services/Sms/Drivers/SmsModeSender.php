<?php

// project: OpenBrigade

namespace App\Services\Sms\Drivers;

use App\Contracts\SmsSender;
use App\Services\Sms\SmsMessage;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * smsmode© driver — sends through the smsmode REST API (api key auth).
 * Configure SMSMODE_API_KEY (stored as the SMS password); the optional
 * sender id falls back to the message's `from`. See docs/admin/sms.md.
 *
 * This is the modern REST API, not the legacy HTTP 1.6 pseudo/pass endpoint
 * the eBrigade app used (fonctions_sms.php · provider 5).
 */
class SmsModeSender implements SmsSender
{
    /** @param array{api_key:?string,endpoint:string} $config */
    public function __construct(private readonly array $config) {}

    public function send(SmsMessage $message): SmsResult
    {
        $apiKey = $this->config['api_key'] ?? null;

        if (empty($apiKey)) {
            Log::warning('SmsMode: missing api key — SMS not sent', ['to' => $message->to]);

            return SmsResult::failed($this->name(), 'smsmode is not configured (API key missing).');
        }

        try {
            $payload = [
                'recipient' => ['to' => $message->to],
                'body' => ['text' => $message->body],
            ];
            if (! empty($message->from)) {
                $payload['from'] = $message->from;
            }

            $response = Http::withHeaders([
                'X-Api-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->asJson()->post(rtrim($this->config['endpoint'], '/').'/sms/v1/messages', $payload);

            if ($response->failed()) {
                return SmsResult::failed($this->name(), 'HTTP '.$response->status().': '.$response->body());
            }

            $reference = $response->json('messageId') ?? $response->json('id');

            return SmsResult::ok($this->name(), $reference !== null ? (string) $reference : null);
        } catch (\Throwable $e) {
            Log::warning('SmsMode: delivery failed', ['to' => $message->to, 'error' => $e->getMessage()]);

            return SmsResult::failed($this->name(), $e->getMessage());
        }
    }

    public function name(): string
    {
        return 'smsmode';
    }
}
