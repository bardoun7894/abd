<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Spec 001 FR-007: plain alert email sent by App\Services\AlertDispatcher.
 */
class AlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $alertTitle;

    public string $alertBody;

    public function __construct(string $title, string $body)
    {
        $this->alertTitle = $title;
        $this->alertBody = $body;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->alertTitle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alert',
            with: [
                'alertTitle' => $this->alertTitle,
                'alertBody' => $this->alertBody,
            ],
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
