<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('tasks')){

            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
            $table->foreignId('branch_id')->nullable()->constrained();
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('company_id')->nullable()->constrained();
            $table->foreignId('contact_person_id')->nullable()->constrained();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->enum('type', ['lead', 'task'])->default('lead');
            $table->string('subject');
            $table->longText('description')->nullable();
            $table->date('due_date')->nullable();
            $table->unsignedTinyInteger('priority')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('status_master_id')->nullable();
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
        Schema::dropIfExists('tasks');
    }
}
