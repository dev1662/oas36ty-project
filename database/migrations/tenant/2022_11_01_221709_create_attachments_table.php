<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!(Schema::hasTable('attachments'))){
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->string('attachment');
            $table->enum('type', ['company', 'task']);
            $table->foreignId('company_id')->constrained();
            $table->foreignId('task_id')->constrained();
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('attachments');
    }
}