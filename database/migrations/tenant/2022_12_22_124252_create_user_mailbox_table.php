<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMailboxTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_mailboxes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('mailbox_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_trash')->default(false);
            $table->boolean('is_spam')->default(false);
            $table->string('message_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_mailboxes');
    }
}
