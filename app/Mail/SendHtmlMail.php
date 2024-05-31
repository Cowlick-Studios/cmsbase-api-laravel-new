<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

use App\Models\tenant\MarketingMailers;
use App\Models\tenant\MarketingMailingList;
use App\Models\tenant\MarketingMailingListSubscribers;

class SendHtmlMail extends Mailable
{
    use Queueable, SerializesModels;

    public $htmlContent;
    public $subject;
    public $unsubscribeLink;

    /**
     * Create a new message instance.
     */
    public function __construct(MarketingMailers $mailer, MarketingMailingList $list, MarketingMailingListSubscribers $subscriber)
    {
        $currentTenant = tenant()->id;

        $this->htmlContent = $mailer->html;
        $this->subject = $mailer->subject;
        $this->unsubscribeLink = config('app.url') . "/tenant/{$currentTenant}/marketing_list/{$list->id}/subscription/unsubscribe/{$subscriber->email}";
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.html_email',
            with: [
                'htmlContent' => $this->htmlContent,
                'unsubscribeLink' => $this->unsubscribeLink
            ]
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
