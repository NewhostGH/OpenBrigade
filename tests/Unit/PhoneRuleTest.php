<?php

use App\Rules\Phone;
use Illuminate\Translation\PotentiallyTranslatedString;

function phoneFails(mixed $value): bool
{
    $failed = false;
    (new Phone)->validate('P_PHONE', $value, function () use (&$failed) {
        $failed = true;

        return new PotentiallyTranslatedString('x', app('translator'));
    });

    return $failed;
}

// TestCase's GeneralSettingService mock serves prefix +33 / min 10 digits.

test('accepts national numbers with enough digits', function () {
    expect(phoneFails('06 12 34 56 78'))->toBeFalse()
        ->and(phoneFails('0612345678'))->toBeFalse();
});

test('accepts international numbers where the prefix replaces the leading zero', function () {
    // +33 6 12 34 56 78 → 9 national digits, counted as 10 with the implied 0.
    expect(phoneFails('+33 6 12 34 56 78'))->toBeFalse();
});

test('rejects numbers with too few digits', function () {
    expect(phoneFails('06 12 34'))->toBeTrue()
        ->and(phoneFails('+33 6 12'))->toBeTrue();
});

test('ignores empty values — presence is the nullable rule\'s concern', function () {
    expect(phoneFails(''))->toBeFalse()
        ->and(phoneFails('   '))->toBeFalse()
        ->and(phoneFails(null))->toBeFalse();
});
