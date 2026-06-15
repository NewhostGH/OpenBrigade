<?php

use App\Support\Habilitations\BaseHabilitations;

/**
 * Derivation of the seeded base-group default grants from the permission
 * classification. A subclass swaps the DB-backed catalog for an in-memory
 * fixture so the matrix logic is exercised without a database.
 *
 * Fixture (id => domain / read / critical):
 *   1 data  write  normal      4 config read  normal
 *   2 data  read   normal      5 data   write critical
 *   3 config write normal      6 config read  critical
 */
function fakeBaseHabilitations(): BaseHabilitations
{
    return new class extends BaseHabilitations
    {
        public function permissions(): array
        {
            $mk = fn (int $id, string $domain, bool $read, bool $critical) => [
                'id' => $id, 'key' => "f{$id}", 'label' => "F{$id}",
                'domain' => $domain, 'is_read' => $read, 'is_critical' => $critical,
                'category' => null, 'ordering' => $id,
            ];

            return [
                1 => $mk(1, 'data', false, false),
                2 => $mk(2, 'data', true, false),
                3 => $mk(3, 'config', false, false),
                4 => $mk(4, 'config', true, false),
                5 => $mk(5, 'data', false, true),
                6 => $mk(6, 'config', true, true),
            ];
        }
    };
}

test('Admin gets every non-critical permission', function () {
    expect(fakeBaseHabilitations()->defaultGrantsFor('admin'))->toEqualCanonicalizing([1, 2, 3, 4]);
});

test('Auditor gets only the read-oriented permissions', function () {
    expect(fakeBaseHabilitations()->defaultGrantsFor('auditor'))->toEqualCanonicalizing([2, 4, 6]);
});

test('User gets the non-critical data domain and no config', function () {
    expect(fakeBaseHabilitations()->defaultGrantsFor('user'))->toEqualCanonicalizing([1, 2]);
});

test('Guest gets a minimal read of the data domain', function () {
    expect(fakeBaseHabilitations()->defaultGrantsFor('guest'))->toEqualCanonicalizing([2]);
});

test('an unknown default yields no grants', function () {
    expect(fakeBaseHabilitations()->defaultGrantsFor('nope'))->toBe([]);
});
