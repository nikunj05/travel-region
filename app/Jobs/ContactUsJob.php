<?php

namespace App\Jobs;

use App\Mail\ContactUsMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class ContactUsJob implements ShouldQueue
{
    use Queueable;

    public $name;
    public $email;
    public $message;

    /**
     * Create a new job instance.
     */
    public function __construct($name, $email, $message)
    {
        $this->name = $name;
        $this->email = $email;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to('info@travelregions.sa', 'Travel Regions Support')
            ->send(new ContactUsMail($this->name, $this->email, $this->message));
    }
}
