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

    /**
     * Create a new message instance.
     */
	public function __construct($booking, $invoicePath)
	{
		$this->booking = $booking;
		$this->invoicePath = $invoicePath;
	}

    
    /**
     * Build the email with an attachment.
     */
    public function build()
    {
        return $this->subject('Booking Confirmation Mail')
            ->markdown('mail.booking_confirmation', [
                'booking' => $this->booking,
            ])
            ->attachData($this->invoicePath, 'invoice.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
