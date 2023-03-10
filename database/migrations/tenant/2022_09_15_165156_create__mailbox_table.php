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
            $table->longText('plainText_messages')->nullable();
            $table->string('attachments')->nullable();
            $table->longText('references')->nullable();
            $table->string('in_reply_to')->nullable();
            $table->boolean('is_parent')->default(null);
            $table->longText('to_replyEmails')->nullable();

            $table->string('label')->nullable();
            $table->boolean('isStarred')->default(false);
            $table->enum('type',['primary','promotions','social'])->default("primary");
            $table->longText('ccaddress')->nullable();
            $table->longText('bccaddress')->nullable();
            $table->string('date')->nullable();
            $table->string('u_date')->nullable();
            $table->string('folder')->nullable();
            $table->string('task_lead_id')->nullable();
            $table->bigInteger('task_id')->nullable();
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
