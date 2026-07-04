<?php

// project: OpenBrigade

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A simple text email rendered through the shared OpenBrigade branded layout.
 * Backs NotificationService::sendEmail() and ad-hoc composed mail; queued so
 * the send never blocks the request.
 */
class PlainMessage extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  string  $bodyText  Plain-text body; blank lines become paragraphs.
     */
    public function __construct(
        public string $subjectLine,
        public string $bodyText,
        public ?string $fromName = null,
        public ?string $fromEmail = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
            from: $this->fromEmail !== null
                ? new Address(
                    $this->fromEmail,
                    $this->fromName ?? (string) config('mail.from.name'),
                )
                : null,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.plain',
            with: [
                'bodyText' => $this->bodyText,
            ],
        );
    }
}
