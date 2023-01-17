<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!(Schema::hasColumn('users','phone'))){
            Schema::table('users', function(Blueprint $table){
                $table->bigInteger('phone')->nullable();
                $table->string('location')->nullable();
                $table->bigInteger('designation_id')->default(1)->nullable();

            });
        }

        if(!(Schema::hasColumn('tasks','mailbox_id'))){
            Schema::table('tasks', function(Blueprint $table){
                $table->bigInteger('mailbox_id')->nullable();
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
