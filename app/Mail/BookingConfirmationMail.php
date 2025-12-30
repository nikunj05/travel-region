<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

	public $invoicePath;
    public $language;

    /**
     * Create a new message instance.
     */
	public function __construct($booking, $invoicePath, $language)
	{
		$this->booking = $booking;
		$this->invoicePath = $invoicePath;
		$this->language = $language;
	}


    /**
     * Build the email with an attachment.
     */
    public function build()
    {
        return $this->subject('Booking Confirmation')
            ->markdown('mail.booking_confirmation', [
                'booking' => $this->booking,
                'language' => $this->language,
            ])
            ->attach($this->invoicePath, [
				'as' => 'invoice.pdf',
				'mime' => 'application/pdf',
			]);
    }
}
