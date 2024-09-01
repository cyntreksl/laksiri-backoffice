<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class GetFeedback extends Mailable
{
    use Queueable, SerializesModels;

    public $customerName;

    public $feedbackURL;

    /**
     * Create a new message instance.
     */
    public function __construct($customerName, $feedbackURL)
    {
        $this->customerName = $customerName;
        $this->feedbackURL = $feedbackURL;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Get Feedback',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.get-feedback',
            with: [
                'customerName' => $this->customerName,
                'feedbackURL' => $this->feedbackURL,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
