<?php

// project: OpenBrigade

namespace App\Services\Sms\Drivers;

use App\Contracts\SmsSender;
use App\Services\Sms\SmsMessage;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * SMSGateway.me driver — sends through an Android device registered on the
 * SMSGateway.me v4 API. Configure SMSGATEWAYME_TOKEN and SMSGATEWAYME_DEVICE_ID;
 * see docs/admin/sms.md. Replaces the legacy lib/SMSGatewayMe integration.
 */
class SmsGatewayMeSender implements SmsSender
{
    /** @param array{token:?string,device_id:?string,endpoint:string} $config */
    public function __construct(private readonly array $config) {}

    public function send(SmsMessage $message): SmsResult
    {
        $token = $this->config['token'] ?? null;
        $deviceId = $this->config['device_id'] ?? null;

        if (empty($token) || empty($deviceId)) {
            Log::warning('SmsGatewayMe: missing token or device id — SMS not sent', [
                'to' => $message->to,
            ]);

            return SmsResult::failed($this->name(), 'SMSGateway.me is not configured (token/device id missing).');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
                'Accept' => 'application/json',
            ])->asJson()->post(rtrim($this->config['endpoint'], '/').'/message/send', [
                [
                    'phone_number' => $message->to,
                    'message' => $message->body,
                    'device_id' => (int) $deviceId,
                ],
            ]);

            if ($response->failed()) {
                return SmsResult::failed($this->name(), 'HTTP '.$response->status().': '.$response->body());
            }

            // v4 returns an array of created messages; grab the first id as reference.
            $reference = $response->json('0.id');

            return SmsResult::ok($this->name(), $reference !== null ? (string) $reference : null);
        } catch (\Throwable $e) {
            Log::warning('SmsGatewayMe: delivery failed', [
                'to' => $message->to,
                'error' => $e->getMessage(),
            ]);

            return SmsResult::failed($this->name(), $e->getMessage());
        }
    }

    public function name(): string
    {
        return 'smsgatewayme';
    }
}
