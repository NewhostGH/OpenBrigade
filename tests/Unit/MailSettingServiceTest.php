<?php

use App\Services\MailSettingService;

/** Fake with fixed stored values (bypasses the DB read). */
function mailSettings(array $values): MailSettingService
{
    return new class($values) extends MailSettingService
    {
        public function __construct(private array $values) {}

        public function get(string $name): string
        {
            return trim($this->values[$name] ?? '');
        }
    };
}

test('leaves the env-driven config untouched when nothing is stored', function () {
    $default = config('mail.default');
    $host = config('mail.mailers.smtp.host');

    // DB-less: the real service reads nothing.
    (new MailSettingService)->apply();
    mailSettings([])->apply();

    expect(config('mail.default'))->toBe($default)
        ->and(config('mail.mailers.smtp.host'))->toBe($host);
});

test('stored rows are the source of truth over the env config', function () {
    mailSettings([
        'mail_mailer' => 'smtp',
        'mail_host' => 'mail.example.org',
        'mail_port' => '465',
        'mail_scheme' => 'smtps',
        'mail_from_address' => 'contact@example.org',
        'mail_from_name' => 'Ma brigade',
    ])->apply();

    expect(config('mail.default'))->toBe('smtp')
        ->and(config('mail.mailers.smtp.host'))->toBe('mail.example.org')
        ->and(config('mail.mailers.smtp.port'))->toBe(465)
        ->and(config('mail.mailers.smtp.scheme'))->toBe('smtps')
        ->and(config('mail.from.address'))->toBe('contact@example.org')
        ->and(config('mail.from.name'))->toBe('Ma brigade');
});

test('an unknown stored mailer is ignored instead of breaking every send', function () {
    $default = config('mail.default');

    mailSettings(['mail_mailer' => 'carrier-pigeon'])->apply();

    expect(config('mail.default'))->toBe($default);
});
