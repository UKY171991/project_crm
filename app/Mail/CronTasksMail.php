<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CronTasksMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pendingProjects;
    public $pendingPayments;
    public $totals;

    /**
     * Create a new message instance.
     */
    public function __construct($pendingProjects, $pendingPayments, $totals = [])
    {
        $this->pendingProjects = $pendingProjects;
        $this->pendingPayments = $pendingPayments;
        $this->totals = $totals;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address(
                config('mail.from.address'), 
                config('mail.from.name')
            ),
            subject: 'Internal Update: Pending Projects and Payments (' . date('M j, Y') . ')',
            tags: ['internal-report', 'daily-update'],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cron_tasks',
            text: 'emails.cron_tasks_text',
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
