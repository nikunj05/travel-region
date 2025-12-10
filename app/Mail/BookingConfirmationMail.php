<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

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
        // $parsedUrl = parse_url($this->invoicePath);
        // $this->invoicePath = ltrim($parsedUrl['path'], '/');

        // $file = Storage::disk('public')->get($filePath);

        return $this->subject('Booking Confirmation Mail')
            ->markdown('mail.booking_confirmation', [
                'booking' => $this->booking,
            ])
            ->attachData($this->invoicePath, 'invoice.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
