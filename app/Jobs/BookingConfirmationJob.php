<?php

namespace App\Jobs;

use App\Mail\BookingConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class BookingConfirmationJob implements ShouldQueue
{
    use Queueable;

    public $booking;
    public $invoicePath;

    /**
     * Create a new job instance.
     */
    public function __construct($booking, $invoicePath)
    {
        $this->booking = $booking;
        $this->invoicePath = $invoicePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->booking->user->email)->send(new BookingConfirmationMail($this->booking, $this->invoicePath));
    }
}
