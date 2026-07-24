<?php

// project: OpenBrigade

namespace App\Services\Sms\Drivers;

use App\Contracts\SmsSender;
use App\Services\Sms\SmsMessage;
use App\Services\Sms\SmsResult;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Generic HTTP driver — covers any simple gateway exposing a URL that carries
 * the message in its query string (e.g. the legacy "SMS Gateway Android"
 * provider 4). Configure SMS_HTTP_URL as a template with any of the
 * placeholders {to} {message} {from} {token}, each rawurlencoded on send, plus
 * SMS_HTTP_METHOD (GET or POST). The secret is stored as the SMS password and
 * substituted for {token}. See docs/admin/sms.md.
 */
class HttpSmsSender implements SmsSender
{
    /** @param array{url:?string,method:string,token:?string} $config */
    public function __construct(private readonly array $config) {}

    public function send(SmsMessage $message): SmsResult
    {
        $template = trim((string) ($this->config['url'] ?? ''));

        if ($template === '') {
            Log::warning('HttpSms: missing url template — SMS not sent', ['to' => $message->to]);

            return SmsResult::failed($this->name(), 'Generic HTTP gateway is not configured (URL missing).');
        }

        try {
            $url = strtr($template, [
                '{to}' => rawurlencode($message->to),
                '{message}' => rawurlencode($message->body),
                '{from}' => rawurlencode((string) $message->from),
                '{token}' => rawurlencode((string) ($this->config['token'] ?? '')),
            ]);

            $method = strtoupper($this->config['method']) === 'POST' ? 'POST' : 'GET';
            $response = Http::send($method, $url);

            if ($response->failed()) {
                return SmsResult::failed($this->name(), 'HTTP '.$response->status().': '.$response->body());
            }

            return SmsResult::ok($this->name());
        } catch (\Throwable $e) {
            Log::warning('HttpSms: delivery failed', ['to' => $message->to, 'error' => $e->getMessage()]);

            return SmsResult::failed($this->name(), $e->getMessage());
        }
    }

    public function name(): string
    {
        return 'http';
    }
}
