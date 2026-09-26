<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SecurityNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $subjectLine,
        public string $bodyText,
        public ?string $actionUrl = null,
        public ?string $actionLabel = null,
    ) {
    }

    public function build(): self
    {
        return $this->subject($this->subjectLine)->view('emails.security-notification');
    }
}
