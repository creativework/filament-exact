<?php

namespace CreativeWork\FilamentExact\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExactErrorMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public $subject = null,
        public $body = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'filament-exact::emails.exact-error',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
