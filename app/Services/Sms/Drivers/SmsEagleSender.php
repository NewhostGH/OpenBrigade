<?php

// project: OpenBrigade

namespace App\Services\Sms\Drivers;

use App\Contracts\SmsSender;
use App\Services\Sms\SmsMessage;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMSEagle driver — sends through a self-hosted SMSEagle appliance over its
 * APIv2 (access-token auth). The appliance host is stored as the SMS API id
 * (bare host or full URL) and the access token as the SMS password.
 * See docs/admin/sms.md. Replaces the legacy provider 8 in fonctions_sms.php.
 */
class SmsEagleSender implements SmsSender
{
    /** @param array{host:?string,token:?string} $config */
    public function __construct(private readonly array $config) {}

    public function send(SmsMessage $message): SmsResult
    {
        $host = trim((string) ($this->config['host'] ?? ''));
        $token = $this->config['token'] ?? null;

        if ($host === '' || empty($token)) {
            Log::warning('SmsEagle: missing host or token — SMS not sent', ['to' => $message->to]);

            return SmsResult::failed($this->name(), 'SMSEagle is not configured (host/token missing).');
        }

        try {
            $response = Http::withHeaders([
                'access-token' => $token,
                'Accept' => 'application/json',
            ])->asJson()->post($this->baseUrl($host).'/api/v2/messages/sms', [
                'to' => [$message->to],
                'text' => $message->body,
            ]);

            if ($response->failed()) {
                return SmsResult::failed($this->name(), 'HTTP '.$response->status().': '.$response->body());
            }

            // APIv2 returns an array of per-recipient results; keep the first id.
            $reference = $response->json('0.id');

            return SmsResult::ok($this->name(), $reference !== null ? (string) $reference : null);
        } catch (\Throwable $e) {
            Log::warning('SmsEagle: delivery failed', ['to' => $message->to, 'error' => $e->getMessage()]);

            return SmsResult::failed($this->name(), $e->getMessage());
        }
    }

    /** Accept a bare host ("10.0.0.5") or a full URL and normalise to an origin. */
    private function baseUrl(string $host): string
    {
        $host = rtrim($host, '/');

        return str_contains($host, '://') ? $host : 'http://'.$host;
    }

    public function name(): string
    {
        return 'smseagle';
    }
}
