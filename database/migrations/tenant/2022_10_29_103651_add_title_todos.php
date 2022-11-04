<?php

use App\Models\Task;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddTitleTodos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            // Task::where('user_id', null)->update([
            //     'status_master_id' => 0
            // ]);
            // Schema::table('to_dos', function (Blueprint $table) {
            //     $table->string('title')->nullable();
            // });
            // Schema::table('tasks', function(Blueprint $table){
            //     $table->foreignId('status_master_id')->change()->default(null)->nullable()->constrained();
            //     // $table->renameColumn('status', 'status_master_id');
            // });
            
        
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
