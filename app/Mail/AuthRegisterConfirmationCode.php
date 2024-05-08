<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthRegisterConfirmationCode extends Mailable
{
    use Queueable, SerializesModels;

    private $verificationCode;
    private $email;
    private $tenant;
    private $actionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct($email, $verificationCode, $tenant = null)
    {
      $this->verificationCode = $verificationCode;
      $this->email = $email;
      $this->tenant = $tenant;

      if($tenant){
        $this->actionUrl = config('app.url') . "/tenant/{$tenant}/auth/register/confirm/{$this->email}/{$this->verificationCode}";
      } else {
        $this->actionUrl = config('app.url') . "/admin/auth/register/confirm/{$this->email}/{$this->verificationCode}";
      }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Verify your account.',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.register.confirmation_code',
            with: [
              'verificationCode' => $this->verificationCode,
              'actionUrl' => $this->actionUrl
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
