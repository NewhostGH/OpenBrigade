<?php

use App\Contracts\SmsSender;
use App\Notifications\Channels\SmsChannel;
use App\Services\Sms\Drivers\ClickatellSender;
use App\Services\Sms\Drivers\HttpSmsSender;
use App\Services\Sms\Drivers\LogSmsSender;
use App\Services\Sms\Drivers\NullSmsSender;
use App\Services\Sms\Drivers\SmsEagleSender;
use App\Services\Sms\Drivers\SmsGatewayMeSender;
use App\Services\Sms\Drivers\SmsModeSender;
use App\Services\Sms\SmsManager;
use App\Services\Sms\SmsMessage;
use App\Services\Sms\SmsResult;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;

// ── Driver resolution ────────────────────────────────────────────────────────

it('resolves the log driver by default and reports success', function () {
    config(['sms.driver' => 'log', 'sms.from' => 'OB']);
    $manager = new SmsManager;

    expect($manager->driver())->toBeInstanceOf(LogSmsSender::class);

    $result = $manager->send('+33600000000', 'hello');
    expect($result->success)->toBeTrue()
        ->and($result->driver)->toBe('log');
});

it('resolves the null driver', function () {
    config(['sms.driver' => 'null']);
    expect((new SmsManager)->driver())->toBeInstanceOf(NullSmsSender::class);
});

it('resolves the smsgatewayme driver', function () {
    config([
        'sms.driver' => 'smsgatewayme',
        'sms.drivers.smsgatewayme' => ['token' => 't', 'device_id' => '1', 'endpoint' => 'https://x/api/v4'],
    ]);
    expect((new SmsManager)->driver())->toBeInstanceOf(SmsGatewayMeSender::class);
});

it('resolves the additional gateway drivers', function () {
    config(['sms.driver' => 'smsmode']);
    expect((new SmsManager)->driver())->toBeInstanceOf(SmsModeSender::class);

    config(['sms.driver' => 'clickatell']);
    expect((new SmsManager)->driver())->toBeInstanceOf(ClickatellSender::class);

    config(['sms.driver' => 'smseagle']);
    expect((new SmsManager)->driver())->toBeInstanceOf(SmsEagleSender::class);

    config(['sms.driver' => 'http']);
    expect((new SmsManager)->driver())->toBeInstanceOf(HttpSmsSender::class);
});

it('throws on an unknown driver', function () {
    config(['sms.driver' => 'carrier-pigeon']);
    (new SmsManager)->driver();
})->throws(InvalidArgumentException::class);

// ── smsmode driver ───────────────────────────────────────────────────────────

it('fails cleanly when smsmode is not configured', function () {
    $result = (new SmsModeSender(['api_key' => null, 'endpoint' => 'https://rest.smsmode.com']))
        ->send(new SmsMessage('+33600000000', 'hi'));

    expect($result->success)->toBeFalse()
        ->and($result->error)->toContain('not configured');
});

it('posts to smsmode with the api key header and returns the reference', function () {
    Http::fake(['*/sms/v1/messages' => Http::response(['messageId' => 'abc-123'], 201)]);

    $result = (new SmsModeSender(['api_key' => 'mode-key', 'endpoint' => 'https://rest.smsmode.com']))
        ->send(new SmsMessage('+33600000000', 'hi', 'OB'));

    expect($result->success)->toBeTrue()
        ->and($result->reference)->toBe('abc-123');

    Http::assertSent(fn ($request) => $request->hasHeader('X-Api-Key', 'mode-key')
        && $request['recipient']['to'] === '+33600000000'
        && $request['body']['text'] === 'hi'
        && $request['from'] === 'OB');
});

// ── Clickatell driver ────────────────────────────────────────────────────────

it('posts to clickatell and treats a non-accepted message as a failure', function () {
    Http::fake(['*/messages' => Http::response(['messages' => [['accepted' => false, 'error' => 'bad number']]], 200)]);

    $result = (new ClickatellSender(['api_key' => 'ck', 'endpoint' => 'https://platform.clickatell.com']))
        ->send(new SmsMessage('33600000000', 'hi'));

    expect($result->success)->toBeFalse()
        ->and($result->error)->toContain('bad number');
});

it('posts to clickatell with a normalised number and returns the api message id', function () {
    Http::fake(['*/messages' => Http::response(['messages' => [['accepted' => true, 'apiMessageId' => 'MSG9']]], 202)]);

    $result = (new ClickatellSender(['api_key' => 'ck', 'endpoint' => 'https://platform.clickatell.com']))
        ->send(new SmsMessage('33600000000', 'hi'));

    expect($result->success)->toBeTrue()
        ->and($result->reference)->toBe('MSG9');

    Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'ck')
        && $request['to'] === ['+33600000000']
        && $request['content'] === 'hi');
});

// ── SMSEagle driver ──────────────────────────────────────────────────────────

it('posts to the smseagle appliance with the access token header', function () {
    Http::fake(['*/api/v2/messages/sms' => Http::response([['id' => 55]], 200)]);

    $result = (new SmsEagleSender(['host' => '10.0.0.5', 'token' => 'eagle']))
        ->send(new SmsMessage('+33600000000', 'hi'));

    expect($result->success)->toBeTrue()
        ->and($result->reference)->toBe('55');

    Http::assertSent(fn ($request) => $request->hasHeader('access-token', 'eagle')
        && $request->url() === 'http://10.0.0.5/api/v2/messages/sms'
        && $request['to'] === ['+33600000000']);
});

// ── Generic HTTP driver ──────────────────────────────────────────────────────

it('fails cleanly when the generic http gateway has no url', function () {
    $result = (new HttpSmsSender(['url' => '', 'method' => 'GET', 'token' => 't']))
        ->send(new SmsMessage('+33600000000', 'hi'));

    expect($result->success)->toBeFalse()
        ->and($result->error)->toContain('not configured');
});

it('substitutes placeholders into the generic http url template', function () {
    Http::fake(['gw.test/*' => Http::response('OK', 200)]);

    $result = (new HttpSmsSender([
        'url' => 'https://gw.test/send?phone={to}&text={message}&key={token}',
        'method' => 'GET',
        'token' => 'sec ret',
    ]))->send(new SmsMessage('+33600000000', 'hé ho'));

    expect($result->success)->toBeTrue();

    Http::assertSent(fn ($request) => $request->url()
        === 'https://gw.test/send?phone=%2B33600000000&text=h%C3%A9%20ho&key=sec%20ret');
});

// ── SMSGateway.me driver ─────────────────────────────────────────────────────

it('fails cleanly when smsgatewayme is not configured', function () {
    $driver = new SmsGatewayMeSender(['token' => null, 'device_id' => null, 'endpoint' => 'https://x/api/v4']);

    $result = $driver->send(new SmsMessage('+33600000000', 'hi'));

    expect($result->success)->toBeFalse()
        ->and($result->error)->toContain('not configured');
});

it('posts to the smsgatewayme endpoint and returns the message reference', function () {
    Http::fake([
        '*/message/send' => Http::response([['id' => 987]], 200),
    ]);

    $driver = new SmsGatewayMeSender(['token' => 'tok', 'device_id' => '42', 'endpoint' => 'https://smsgateway.me/api/v4']);
    $result = $driver->send(new SmsMessage('+33600000000', 'hi', 'OB'));

    expect($result->success)->toBeTrue()
        ->and($result->reference)->toBe('987');

    Http::assertSent(function ($request) {
        return $request->hasHeader('Authorization', 'tok')
            && $request[0]['phone_number'] === '+33600000000'
            && $request[0]['device_id'] === 42;
    });
});

// ── Default from applied ─────────────────────────────────────────────────────

it('applies the configured default sender when none is given', function () {
    config(['sms.driver' => 'log', 'sms.from' => 'BRIGADE']);
    $captured = null;

    $manager = new class extends SmsManager
    {
        public ?SmsMessage $seen = null;

        public function driver(): SmsSender
        {
            return new class($this) implements SmsSender
            {
                public function __construct(private $parent) {}

                public function send(SmsMessage $m): SmsResult
                {
                    $this->parent->seen = $m;

                    return SmsResult::ok('spy');
                }

                public function name(): string
                {
                    return 'spy';
                }
            };
        }
    };

    $manager->send('+33600000000', 'body');
    expect($manager->seen->from)->toBe('BRIGADE');
});

// ── Notification SMS channel ─────────────────────────────────────────────────

it('routes a notification through the sms channel', function () {
    config(['sms.driver' => 'log']);
    $spy = new class extends SmsManager
    {
        public array $sent = [];

        public function sendMessage(SmsMessage $m): SmsResult
        {
            $this->sent[] = $m;

            return SmsResult::ok('spy');
        }
    };

    $channel = new SmsChannel($spy);

    $notifiable = new class
    {
        public function routeNotificationFor($channel, $notification = null)
        {
            return '+33611112222';
        }
    };

    $notification = new class extends Notification
    {
        public function toSms($notifiable): string
        {
            return 'coucou';
        }
    };

    $channel->send($notifiable, $notification);

    expect($spy->sent)->toHaveCount(1)
        ->and($spy->sent[0]->to)->toBe('+33611112222')
        ->and($spy->sent[0]->body)->toBe('coucou');
});

it('does not send when the notifiable has no sms route', function () {
    $spy = new class extends SmsManager
    {
        public array $sent = [];

        public function sendMessage(SmsMessage $m): SmsResult
        {
            $this->sent[] = $m;

            return SmsResult::ok('spy');
        }
    };

    $channel = new SmsChannel($spy);
    $notifiable = new class
    {
        public function routeNotificationFor($channel, $notification = null)
        {
            return null;
        }
    };
    $notification = new class extends Notification
    {
        public function toSms($n): string
        {
            return 'x';
        }
    };

    $channel->send($notifiable, $notification);
    expect($spy->sent)->toHaveCount(0);
});
