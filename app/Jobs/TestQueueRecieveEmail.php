<?php

namespace App\Jobs;

use App\Models\Mailbox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;

class TestQueueRecieveEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    // protected $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->data = $data;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
    //   echo response()->json('Fetching Mails will take several minutes.Please check after some time');

        Artisan::call('fetch:emails');
    }
}
