<?php

// project: OpenBrigade

namespace App\Services\Sms\Drivers;

use App\Contracts\SmsSender;
use App\Services\Sms\SmsMessage;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Clickatell driver — sends through the Clickatell one-API REST endpoint
 * (platform.clickatell.com). Configure CLICKATELL_API_KEY (stored as the SMS
 * password); the number is normalised to +E164. See docs/admin/sms.md.
 *
 * Replaces the legacy provider 6 in fonctions_sms.php.
 */
class ClickatellSender implements SmsSender
{
    /** @param array{api_key:?string,endpoint:string} $config */
    public function __construct(private readonly array $config) {}

    public function send(SmsMessage $message): SmsResult
    {
        $apiKey = $this->config['api_key'] ?? null;

        if (empty($apiKey)) {
            Log::warning('Clickatell: missing api key — SMS not sent', ['to' => $message->to]);

            return SmsResult::failed($this->name(), 'Clickatell is not configured (API key missing).');
        }

        try {
            $to = str_starts_with($message->to, '+') ? $message->to : '+'.$message->to;

            $response = Http::withHeaders([
                'Authorization' => $apiKey,
                'Accept' => 'application/json',
            ])->asJson()->post(rtrim($this->config['endpoint'], '/').'/messages', [
                'content' => $message->body,
                'to' => [$to],
            ]);

            if ($response->failed()) {
                return SmsResult::failed($this->name(), 'HTTP '.$response->status().': '.$response->body());
            }

            // A message is queued only when its "accepted" flag is true.
            if (! $response->json('messages.0.accepted')) {
                $error = $response->json('messages.0.error') ?? $response->body();

                return SmsResult::failed($this->name(), is_string($error) ? $error : json_encode($error));
            }

            $reference = $response->json('messages.0.apiMessageId');

            return SmsResult::ok($this->name(), $reference !== null ? (string) $reference : null);
        } catch (\Throwable $e) {
            Log::warning('Clickatell: delivery failed', ['to' => $message->to, 'error' => $e->getMessage()]);

            return SmsResult::failed($this->name(), $e->getMessage());
        }
    }

    public function name(): string
    {
        return 'clickatell';
    }
}
