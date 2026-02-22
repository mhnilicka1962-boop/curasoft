<?php

namespace App\Mail;

use App\Models\Benutzer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EinladungMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Benutzer $benutzer,
        public string $link,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Einladung zu ' . config('theme.app_name', 'CuraSoft'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.einladung',
        );
    }
}
