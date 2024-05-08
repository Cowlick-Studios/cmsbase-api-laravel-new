<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthPasswordResetConfirmationCode extends Mailable
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
        $this->actionUrl = config('app.url') . "/tenant/{$tenant}/auth/password_reset/confirm/{$this->email}/{$this->verificationCode}";
      } else {
        $this->actionUrl = config('app.url') . "/admin/auth/password_reset/confirm/{$this->email}/{$this->verificationCode}";
      }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password reset code.',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.password_reset.confirmation_code',
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
