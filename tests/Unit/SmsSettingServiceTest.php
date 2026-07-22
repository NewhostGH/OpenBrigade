<?php

use App\Services\SmsSettingService;

/** Fake with fixed stored values (bypasses the DB read). */
function smsSettings(array $values): SmsSettingService
{
    return new class($values) extends SmsSettingService
    {
        public function __construct(private array $values) {}

        public function get(string $name): string
        {
            return trim($this->values[$name] ?? '');
        }
    };
}

test('falls back to the env driver when no provider is stored', function () {
    config(['sms.driver' => 'log']);

    // DB-less: the real service reads nothing and must defer to config.
    expect((new SmsSettingService)->driver())->toBe('log')
        ->and(smsSettings([])->driver())->toBe('log');
});

test('maps stored providers onto the driver set', function () {
    expect(smsSettings(['sms_provider' => 'log'])->driver())->toBe('log')
        ->and(smsSettings(['sms_provider' => 'none'])->driver())->toBe('null')
        ->and(smsSettings(['sms_provider' => 'SMSGateway.me'])->driver())->toBe('smsgatewayme')
        ->and(smsSettings(['sms_provider' => 'smsgatewayme'])->driver())->toBe('smsgatewayme');
});

test('an unknown provider disables SMS instead of erroring', function () {
    expect(smsSettings(['sms_provider' => 'clickatell'])->driver())->toBe('null');
});

test('smsgatewayme credentials come from settings with env fallback', function () {
    config(['sms.drivers.smsgatewayme' => ['token' => 'env-token', 'device_id' => 'env-device', 'endpoint' => 'https://smsgateway.me/api/v4']]);

    $stored = smsSettings(['sms_password' => 'db-token', 'sms_api_id' => 'db-device']);
    expect($stored->smsGatewayMeOptions())->toMatchArray(['token' => 'db-token', 'device_id' => 'db-device']);

    $empty = smsSettings([]);
    expect($empty->smsGatewayMeOptions())->toMatchArray(['token' => 'env-token', 'device_id' => 'env-device']);
});
