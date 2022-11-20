<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mailbox', function (Blueprint $table) {
            $table->id();
            $table->string('message_id')->nullable();
            $table->string('avatar')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('to_email')->nullable();
            $table->string('subject')->nullable();
            $table->longText('message')->nullable();
            $table->string('attachments')->nullable();
            $table->string('references')->nullable();
            $table->string('in_reply_to')->nullable();
            $table->boolean('is_parent')->default(null);

            $table->string('label')->nullable();
            $table->boolean('isStarred')->default(false);
            $table->enum('type',['primary','promotions','social'])->default("primary");
            $table->string('date')->nullable();
            $table->string('u_date')->nullable();
            $table->string('folder')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mailbox');
    }
}
