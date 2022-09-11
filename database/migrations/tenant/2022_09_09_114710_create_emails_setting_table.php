<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmailsSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('emails_settings', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->enum('inbound_status', ['tick', 'cross', 'alert'])->default('cross');
            $table->enum('outbound_status', ['tick', 'cross', 'alert'])->default('cross');
            
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
        Schema::dropIfExists('emails_settings');
    }
}
