<?php

namespace App\Mail;

use App\Models\Benutzer;
use App\Models\Organisation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Zeitnachweismail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Benutzer      $benutzer,
        public readonly Organisation  $org,
        public readonly string        $pdfInhalt,
        public readonly string        $dateiname,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->org->email ?? config('mail.from.address'),
            subject: "Zeitnachweis {$this->dateiname} — {$this->org->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.zeitnachweis',
            with: [
                'benutzer' => $this->benutzer,
                'org'      => $this->org,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(fn() => $this->pdfInhalt, $this->dateiname)
                ->withMime('application/pdf'),
        ];
    }
}
