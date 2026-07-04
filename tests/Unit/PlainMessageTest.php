<?php

use App\Mail\PlainMessage;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * The queued branded mailable that backs NotificationService::sendEmail().
 */
it('is queued so sends never block the request', function () {
    expect(new PlainMessage('Sujet', 'Corps'))->toBeInstanceOf(ShouldQueue::class);
});

it('carries the given subject and renders the branded markdown view', function () {
    $mail = new PlainMessage('Renouvellement', "Ligne 1\nLigne 2");

    expect($mail->envelope()->subject)->toBe('Renouvellement')
        ->and($mail->content()->markdown)->toBe('mail.plain')
        ->and($mail->content()->with['bodyText'])->toBe("Ligne 1\nLigne 2");
});

it('applies a custom from address and name when provided', function () {
    $mail = new PlainMessage('S', 'B', 'Section Nord', 'nord@brigade.test');
    $from = $mail->envelope()->from;

    expect($from)->not->toBeNull()
        ->and($from->address)->toBe('nord@brigade.test')
        ->and($from->name)->toBe('Section Nord');
});

it('leaves the from unset so the global default applies when none is given', function () {
    expect((new PlainMessage('S', 'B'))->envelope()->from)->toBeNull();
});
