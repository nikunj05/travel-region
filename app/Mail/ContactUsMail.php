<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactUsMail extends Mailable
{
    use Queueable, SerializesModels;

    public $name;
    public $email;
    public $message;

    /**
     * Create a new message instance.
     */
    public function __construct($name, $email, $message)
    {
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
    }

    /**
     * Build the email with an attachment.
     */
    public function build()
    {
        return $this->subject('Travel Regions - Contact Us')
            ->from($this->email, $this->name)
            ->markdown('mail.contact_us', [
                'name' => $this->name,
                'email' => $this->email,
                'message' => $this->message,
            ]);
    }
}
