<?php

use App\Services\FeatureService;
use App\Services\NavigationService;

/**
 * Focused unit tests for the navbar active-item resolution: the active item is
 * the LONGEST nav path that is a boundary-prefix of the current path, so a
 * parent path never lights up while on a child. Exercised via reflection
 * against the real navigation config — no database required.
 */
function navActivePath(string $currentPath): string
{
    $svc = new NavigationService(Mockery::mock(FeatureService::class));
    $ref = new ReflectionMethod($svc, 'activePath');

    return $ref->invoke($svc, $currentPath);
}

test('each guard view resolves to its own nav item', function () {
    expect(navActivePath('/duty/today'))->toBe('/duty/today');
    expect(navActivePath('/duty/weekly'))->toBe('/duty/weekly');
    expect(navActivePath('/duty/monthly'))->toBe('/duty/monthly');
});

test('a deeper sub-path still resolves to its longest registered ancestor', function () {
    // /duty/monthly/export/xls is not its own nav item, so the monthly item wins.
    expect(navActivePath('/duty/monthly/export/xls'))->toBe('/duty/monthly');
});

test('an unmatched path yields no active item', function () {
    expect(navActivePath('/nowhere'))->toBe('');
});

test('pathMatches only matches on a segment boundary, not a substring', function () {
    $svc = new NavigationService(Mockery::mock(FeatureService::class));
    $ref = new ReflectionMethod($svc, 'pathMatches');

    // /duty is an ancestor of /duty/monthly …
    expect($ref->invoke($svc, '/duty/monthly', '/duty'))->toBeTrue();
    // … but not of /duty-roster (no segment boundary).
    expect($ref->invoke($svc, '/duty-roster', '/duty'))->toBeFalse();
    // Exact match holds.
    expect($ref->invoke($svc, '/duty', '/duty'))->toBeTrue();
});
