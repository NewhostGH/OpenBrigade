<?php

use App\Services\FinancialReportService;
use App\Services\SectionScopeService;

/**
 * Unit coverage for the pure pivot/total assembly of the financial report
 * (legacy report_cotisations.php). The scope service is only needed to
 * construct the service; assemble() itself never touches it or the database.
 */
function makeFinancialReportService(): FinancialReportService
{
    return new FinancialReportService(Mockery::mock(SectionScopeService::class));
}

test('assemble pivots amounts, rejets and totals per section', function () {
    $service = makeFinancialReportService();

    $sections = [
        ['S_ID' => 1, 'label' => 'A — Alpha'],
        ['S_ID' => 2, 'label' => 'B — Beta'],
    ];
    $effectifs = [1 => 10, 2 => 5];
    $paymentTypes = [1 => 'Espèces', 2 => 'Chèque'];
    $cotis = [
        1 => [
            'SPV' => [1 => 100.0, 2 => 50.0],
            '' => [1 => 20.0],
        ],
        2 => [
            'SPP' => [2 => 30.0],
        ],
    ];
    $rejets = [
        1 => ['SPV' => 10.0],
    ];

    $report = $service->assemble($sections, $effectifs, $cotis, $rejets, $paymentTypes);

    // Payment-type columns are preserved in order.
    expect($report['paymentTypes'])->toBe([1 => 'Espèces', 2 => 'Chèque']);

    // Two sections, in the order supplied.
    expect($report['sections'])->toHaveCount(2);

    $alpha = $report['sections'][0];
    expect($alpha['label'])->toBe('A — Alpha');
    expect($alpha['effectifs'])->toBe(10);

    // Empty profession sorts first and is displayed with the placeholder.
    expect($alpha['lines'][0]['profession'])->toBe(FinancialReportService::NO_PROFESSION);
    expect($alpha['lines'][0]['amounts'])->toBe([1 => 20.0, 2 => 0.0]);
    expect($alpha['lines'][0]['total'])->toBe(20.0);

    // SPV line: 100 + 50 collected, 10 rejected → net 140.
    expect($alpha['lines'][1]['profession'])->toBe('SPV');
    expect($alpha['lines'][1]['amounts'])->toBe([1 => 100.0, 2 => 50.0]);
    expect($alpha['lines'][1]['rejets'])->toBe(10.0);
    expect($alpha['lines'][1]['total'])->toBe(140.0);

    // Section subtotal aggregates both lines.
    expect($alpha['subtotal']['amounts'])->toBe([1 => 120.0, 2 => 50.0]);
    expect($alpha['subtotal']['rejets'])->toBe(10.0);
    expect($alpha['subtotal']['total'])->toBe(160.0);
});

test('assemble sums grand totals across every section', function () {
    $service = makeFinancialReportService();

    $report = $service->assemble(
        [['S_ID' => 1, 'label' => 'A'], ['S_ID' => 2, 'label' => 'B']],
        [1 => 10, 2 => 5],
        [
            1 => ['SPV' => [1 => 100.0, 2 => 50.0]],
            2 => ['SPP' => [2 => 30.0]],
        ],
        [1 => ['SPV' => 10.0]],
        [1 => 'Espèces', 2 => 'Chèque'],
    );

    expect($report['totals']['effectifs'])->toBe(15);
    expect($report['totals']['amounts'])->toBe([1 => 100.0, 2 => 80.0]);
    expect($report['totals']['rejets'])->toBe(10.0);
    expect($report['totals']['total'])->toBe(170.0);
});

test('assemble emits a zeroed subtotal for a section with headcount but no payments', function () {
    $service = makeFinancialReportService();

    $report = $service->assemble(
        [['S_ID' => 3, 'label' => 'C — Gamma']],
        [3 => 7],
        [],
        [],
        [1 => 'Espèces'],
    );

    $gamma = $report['sections'][0];
    expect($gamma['lines'])->toBe([]);
    expect($gamma['effectifs'])->toBe(7);
    expect($gamma['subtotal']['total'])->toBe(0.0);
    expect($report['totals']['effectifs'])->toBe(7);
    expect($report['totals']['total'])->toBe(0.0);
});
