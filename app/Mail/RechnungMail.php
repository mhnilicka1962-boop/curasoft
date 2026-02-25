<?php

namespace App\Mail;

use App\Models\Organisation;
use App\Models\Rechnung;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RechnungMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Rechnung $rechnung,
        public readonly Organisation $org,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: $this->org->email ?? config('mail.from.address'),
            subject: "Rechnung {$this->rechnung->rechnungsnummer} — {$this->org->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.rechnung',
            with: [
                'rechnung' => $this->rechnung,
                'org'      => $this->org,
                'klient'   => $this->rechnung->klient,
            ],
        );
    }

    public function attachments(): array
    {
        if ($this->rechnung->pdf_pfad && Storage::exists($this->rechnung->pdf_pfad)) {
            return [
                Attachment::fromStorageDisk('local', $this->rechnung->pdf_pfad)
                    ->as($this->rechnung->rechnungsnummer . '.pdf')
                    ->withMime('application/pdf'),
            ];
        }
        return [];
    }
}
