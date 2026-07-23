<?php

use App\Http\Controllers\EventController;
use App\Models\User;
use App\Services\GeneralSettingService;
use Mockery\MockInterface;

/** Invoke the private guard on a container-built controller. */
function photoGuardBlocks(int $targetPid): bool
{
    $method = new ReflectionMethod(EventController::class, 'selfRegistrationBlockedByPhoto');

    return (bool) $method->invoke(app(EventController::class), $targetPid);
}

function photoGuardUser(?string $photo): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill([
        'P_ID' => 7, 'P_NOM' => 'Test', 'P_PRENOM' => 'User',
        'P_SECTION' => 1, 'P_ACTIF' => 1, 'P_PHOTO' => $photo,
        'P_MDP' => bcrypt('secret'),
    ]);

    return $user;
}

function photoRequiredSetting(bool $on): void
{
    $general = Mockery::mock(GeneralSettingService::class)->makePartial();
    $general->shouldReceive('get')->andReturnUsing(
        fn (string $name) => $name === 'photo_obligatoire' ? ($on ? '1' : '0') : '0'
    );
    app()->instance(GeneralSettingService::class, $general);
}

test('blocks a self-registration without a photo when the setting is on', function () {
    photoRequiredSetting(true);
    $this->actingAs(photoGuardUser(null));

    expect(photoGuardBlocks(7))->toBeTrue();
});

test('never blocks registering someone else', function () {
    photoRequiredSetting(true);
    $this->actingAs(photoGuardUser(null));

    expect(photoGuardBlocks(42))->toBeFalse();
});

test('does not block when the member has a photo', function () {
    photoRequiredSetting(true);
    $this->actingAs(photoGuardUser('photos/7.jpg'));

    expect(photoGuardBlocks(7))->toBeFalse();
});

test('does not block when the setting is off', function () {
    photoRequiredSetting(false);
    $this->actingAs(photoGuardUser(null));

    expect(photoGuardBlocks(7))->toBeFalse();
});
