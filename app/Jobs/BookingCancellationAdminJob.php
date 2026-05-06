<?php

namespace App\Jobs;

use App\Mail\BookingCancellationAdminMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class BookingCancellationAdminJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $booking;

    /**
     * Create a new job instance.
     */
    public function __construct($booking)
    {
        $this->booking = $booking;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to('info@travelregions.sa', 'Travel Regions Support')
            ->send(new BookingCancellationAdminMail($this->booking));
    }
}
