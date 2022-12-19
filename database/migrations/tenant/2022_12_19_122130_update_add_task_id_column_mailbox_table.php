<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAddTaskIdColumnMailboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //task_lead_id
        if(!(Schema::hasColumn('mailbox','task_lead_id'))){
            Schema::table('mailbox', function(Blueprint $table){
                $table->bigInteger('task_lead_id')->nullable();
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
