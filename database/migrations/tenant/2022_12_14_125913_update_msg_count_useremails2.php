<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMsgCountUseremails2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!(Schema::hasColumn('user_emails','inbound_msg_count'))){
            Schema::table('user_emails', function(Blueprint $table){
                $table->bigInteger('inbound_msg_count')->default(0);
            });
        }
        if(!(Schema::hasColumn('user_emails','sent_msg_count'))){
            Schema::table('user_emails', function(Blueprint $table){
                $table->bigInteger('sent_msg_count')->default(0);
            });
        }
        if(!(Schema::hasColumn('user_emails','trash_msg_count'))){
            Schema::table('user_emails', function(Blueprint $table){
                $table->bigInteger('trash_msg_count')->default(0);
            });
        }
        if(!(Schema::hasColumn('user_emails','spam_msg_count'))){
            Schema::table('user_emails', function(Blueprint $table){
                $table->bigInteger('spam_msg_count')->default(0);
            });
        }
        if(!(Schema::hasColumn('user_emails','draft_msg_count'))){
            Schema::table('user_emails', function(Blueprint $table){
                $table->bigInteger('draft_msg_count')->default(0);
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
