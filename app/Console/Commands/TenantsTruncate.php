<?php

namespace App\Console\Commands;

use App\Models\Mailbox;
use App\Models\MailboxAttachment;
use App\Models\Tenant;
use App\Models\UserEmail;
use App\Models\UserMailbox;
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
            MailboxAttachment::query()->truncate();
            UserEmail::where('id','!=',null)->update(['inbound_msg_count'=> 0, 'sent_msg_count'=> 0, 'trash_msg_count'=> 0, 'spam_msg_count'=> 0, 'draft_msg_count'=> 0]);
            UserMailbox::query()->truncate();
        });
    }
}
