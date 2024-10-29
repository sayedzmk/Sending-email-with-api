<?php

namespace App\Listeners;

use App\Events\VerifyEmailByCode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class VerifyEmailByCodeFired
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(VerifyEmailByCode $event): void
    {
        dd($event);
    }
}
