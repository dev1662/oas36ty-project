<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMailboxAttachSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!(Schema::hasColumn('mailbox_attachments','attachment_url'))){
            Schema::table('mailbox_attachments', function(Blueprint $table){
                $table->string('attachment_url')->nullable();
            });
        }
        if(!(Schema::hasColumn('mailbox_attachments','attachment_name'))){
            Schema::table('mailbox_attachments', function(Blueprint $table){
                $table->string('attachment_name')->nullable();
            });
        }
        if(!(Schema::hasColumn('mailbox_attachments','folder'))){
            Schema::table('mailbox_attachments', function(Blueprint $table){
                $table->string('folder')->nullable();
            });
        }
        if((Schema::hasColumn('mailbox_attachments','attachment'))){
            Schema::table('mailbox_attachments', function(Blueprint $table){
                $table->dropColumn(['attachment']);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
