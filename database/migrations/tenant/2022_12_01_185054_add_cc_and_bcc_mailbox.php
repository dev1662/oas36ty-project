<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCcAndBccMailbox extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!(Schema::hasColumn('mailbox','ccaddress'))){
            Schema::table('mailbox', function(Blueprint $table){
                $table->longText('ccaddress')->nullable();
            });
        }
        if(!(Schema::hasColumn('mailbox','bccaddress'))){
            Schema::table('mailbox', function(Blueprint $table){
                $table->longText('bccaddress')->nullable();
            });
        }
        if(!(Schema::hasColumn('mailbox','to_replyEmails'))){
            Schema::table('mailbox', function(Blueprint $table){
                $table->longText('to_replyEmails')->nullable();
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
