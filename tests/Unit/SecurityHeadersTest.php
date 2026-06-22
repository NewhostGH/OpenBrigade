<?php

use App\Http\Middleware\SecurityHeaders;
use App\Services\SecuritySettingService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

function headersSettings(array $overrides = []): SecuritySettingService
{
    return new class($overrides) extends SecuritySettingService
    {
        public function __construct(private array $overrides) {}

        public function get(string $name): string
        {
            return (string) ($this->overrides[$name] ?? parent::default($name));
        }
    };
}

function runHeaders(SecuritySettingService $settings, Request $request): Response
{
    return (new SecurityHeaders($settings))->handle($request, fn () => new Response('ok'));
}

it('always sets the static security headers', function () {
    $response = runHeaders(headersSettings(), Request::create('http://example.test/'));

    expect($response->headers->get('X-Frame-Options'))->toBe('SAMEORIGIN')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
});

it('emits CSP by default and omits it when disabled', function () {
    $on = runHeaders(headersSettings(), Request::create('http://example.test/'));
    expect($on->headers->get('Content-Security-Policy'))->toContain("default-src 'self'");

    $off = runHeaders(headersSettings(['sec_csp_enabled' => '0']), Request::create('http://example.test/'));
    expect($off->headers->has('Content-Security-Policy'))->toBeFalse();
});

it('uses the report-only header name in report-only mode', function () {
    $response = runHeaders(
        headersSettings(['sec_csp_report_only' => '1']),
        Request::create('http://example.test/'),
    );

    expect($response->headers->has('Content-Security-Policy'))->toBeFalse()
        ->and($response->headers->get('Content-Security-Policy-Report-Only'))->toContain("default-src 'self'");
});

it('only sends HSTS over HTTPS when enabled', function () {
    $settings = headersSettings(['sec_hsts_enabled' => '1', 'sec_hsts_max_age' => '100']);

    $http = runHeaders($settings, Request::create('http://example.test/'));
    expect($http->headers->has('Strict-Transport-Security'))->toBeFalse();

    $https = runHeaders($settings, Request::create('https://example.test/'));
    expect($https->headers->get('Strict-Transport-Security'))->toBe('max-age=100; includeSubDomains');
});

it('never sends HSTS when disabled even over HTTPS', function () {
    $response = runHeaders(headersSettings(), Request::create('https://example.test/'));

    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});
