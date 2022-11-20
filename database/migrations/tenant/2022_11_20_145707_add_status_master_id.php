<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusMasterId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::table('tasks')->hasColumn('tasks', 'status')){
            Schema::table('tasks', function (Blueprint $table){
                $table->dropColumn('status');
                $table->unsignedBigInteger('status_master_id')->nullable();
            });
        }

        if(!Schema::table('mailbox')->hasColumn('mailbox', 'references')){
            Schema::table('mailbox', function (Blueprint $table){
      
                $table->string('references')->nullable();
            });

        }elseif(!Schema::table('mailbox')->hasColumn('mailbox', 'in_reply_to')){
            Schema::table('mailbox', function (Blueprint $table){
      
                $table->string('in_reply_to')->nullable();
            });
        }elseif(!Schema::table('mailbox')->hasColumn('mailbox', 'is_parent')){
            Schema::table('mailbox', function (Blueprint $table){
                $table->boolean('is_parent')->default(null);
      
            });
        }else{

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
