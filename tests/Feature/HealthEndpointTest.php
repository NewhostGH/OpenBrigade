<?php

it('exposes a public health endpoint returning JSON', function () {
    $response = $this->get('/health');

    $response->assertOk()
        ->assertJsonStructure([
            'status',
            'version',
            'timestamp',
            'checks' => [
                'database' => ['status'],
                'cache' => ['status'],
                'storage' => ['status'],
                'disk' => ['status'],
                'clamav' => ['status'],
            ],
        ]);

    expect($response->json('status'))->not->toBe('down');
});

it('does not require authentication for the health endpoint', function () {
    // No actingAs — an anonymous uptime probe must still get a report.
    $this->get('/health')->assertOk();
});
