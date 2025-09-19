<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $time = 0;

    /**
     * Create a new job instance.
     */
    public function __construct($time)
    {
        $this->time = $time;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info("Start $this->time seconds job");
        sleep($this->time);
        Log::info("End $this->time seconds job");
    }
}
