<?php

namespace App\Console\Commands;

use App\Models\Mailbox;
use App\Models\Tenant;
use Illuminate\Console\Command;

class TenantsTruncate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'truncate all the tenants mailbox tables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tenants = Tenant::select('id')->get();
        tenancy()->runForMultiple($tenants, function ($tenants) {
            Mailbox::query()->truncate();
        });
    }
}
