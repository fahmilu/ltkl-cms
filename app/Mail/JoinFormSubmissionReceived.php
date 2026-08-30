<?php

namespace App\Mail;

use App\Filament\Resources\JoinFormSubmissions\JoinFormSubmissionResource;
use App\Models\JoinFormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JoinFormSubmissionReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public JoinFormSubmission $submission) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New join form submission from ' . $this->submission->name,
            replyTo: [$this->submission->email],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.join-form-submission',
            with: [
                // The panel is named explicitly so the URL resolves outside of
                // a panel request too, e.g. from a queue worker.
                'url' => JoinFormSubmissionResource::getUrl('view', ['record' => $this->submission], panel: 'administrator'),
            ],
        );
    }
}
