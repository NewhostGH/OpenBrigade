<?php

use App\Http\Controllers\PersonnelController;
use App\Models\Country;
use App\Models\Personnel;
use App\Models\Section;
use App\Models\User;
use App\Services\FeatureService;
use App\Services\PermissionResolver;
use App\Services\PersonnelExportService;
use App\Services\QrCodeService;
use App\Services\SectionScopeService;
use Mockery\MockInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

// ── Helpers ──────────────────────────────────────────────────────────────────

/** A Personnel with its section/country relations pre-set so no DB is touched. */
function qrPersonnel(array $attrs = [], ?string $sectionCode = 'CIS', ?string $country = null): Personnel
{
    $personnel = new Personnel;
    $personnel->forceFill(array_merge([
        'P_ID' => 1,
        'P_NOM' => 'Durand',
        'P_PRENOM' => 'jean',
        'P_CIVILITE' => 0,
    ], $attrs));

    $personnel->setRelation('section', $sectionCode ? (new Section)->forceFill(['S_CODE' => $sectionCode]) : null);
    $personnel->setRelation('country', $country ? (new Country)->forceFill(['NAME' => $country]) : null);

    return $personnel;
}

/** A controller instance with mocked (unused) constructor dependencies. */
function qrController(): PersonnelController
{
    return new PersonnelController(
        Mockery::mock(FeatureService::class),
        Mockery::mock(SectionScopeService::class),
        Mockery::mock(PermissionResolver::class),
    );
}

/** A User whose hasPermission() returns the given verdict. */
function qrUser(int $pid, bool $canAdmin): User
{
    /** @var User&MockInterface $user */
    $user = Mockery::mock(User::class)->makePartial();
    $user->forceFill(['P_ID' => $pid]);
    $user->shouldReceive('hasPermission')->andReturn($canAdmin);

    return $user;
}

// ── QrCodeService ─────────────────────────────────────────────────────────────

test('QrCodeService renders an inline SVG document', function () {
    $svg = app(QrCodeService::class)->svg('https://openbrigade.test');

    expect($svg)->toContain('<svg')
        ->and($svg)->not->toContain('<?xml');
});

test('QrCodeService renders a data URI', function () {
    expect(app(QrCodeService::class)->svgDataUri('hello'))
        ->toStartWith('data:image/svg+xml');
});

// ── Identity payload ──────────────────────────────────────────────────────────

test('buildQrText assembles the identity card, omitting empty fields', function () {
    $personnel = qrPersonnel([
        'P_NOM' => 'Durand',
        'P_PRENOM' => 'jean',
        'P_SEXE' => 'M',
        'P_BIRTHPLACE' => 'Lyon',
        'P_EMAIL' => 'jean@example.test',
    ], sectionCode: 'CIS-01', country: 'France');

    $text = (new PersonnelExportService)->buildQrText($personnel);

    expect($text)->toContain('DURAND')
        ->and($text)->toContain('Jean')
        ->and($text)->toContain('Né à Lyon')
        ->and($text)->toContain('Nationalité : France')
        ->and($text)->toContain('Section : CIS-01')
        ->and($text)->toContain('Email : jean@example.test')
        // No phone/address were set, so those labels must not appear.
        ->and($text)->not->toContain('Téléphone')
        ->and($text)->not->toContain('Adresse');
});

test('buildQrText marks a female member with the feminine form', function () {
    $text = (new PersonnelExportService)->buildQrText(
        qrPersonnel(['P_SEXE' => 'F', 'P_BIRTHPLACE' => 'Paris'])
    );

    expect($text)->toContain('Née à Paris');
});

// ── Authorization ─────────────────────────────────────────────────────────────

test('a member can view their own identity QR code', function () {
    $this->actingAs(qrUser(1, canAdmin: false));

    $view = qrController()->qrCode(qrPersonnel(['P_ID' => 1]), app(QrCodeService::class));

    expect($view->name())->toBe('personnel.qr-code')
        ->and($view->getData()['qrSvg'])->toContain('<svg');
});

test('viewing another member without technical-admin permission is forbidden', function () {
    $this->actingAs(qrUser(1, canAdmin: false));

    expect(fn () => qrController()->qrCode(qrPersonnel(['P_ID' => 2]), app(QrCodeService::class)))
        ->toThrow(HttpException::class);
});

test('a technical admin can view another member identity QR code', function () {
    $this->actingAs(qrUser(1, canAdmin: true));

    $view = qrController()->qrCode(qrPersonnel(['P_ID' => 2]), app(QrCodeService::class));

    expect($view->name())->toBe('personnel.qr-code');
});

// ── Route registration ─────────────────────────────────────────────────────────

test('the personnel qr-code route is registered', function () {
    expect(route('personnel.qr-code', ['personnel' => 7]))
        ->toContain('/personnel/7/qr-code');
});

test('unauthenticated users are redirected from the qr-code page to login', function () {
    $this->get('/personnel/1/qr-code')->assertRedirect('/login');
});
