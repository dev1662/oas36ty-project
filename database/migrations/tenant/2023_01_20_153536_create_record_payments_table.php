<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecordPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('record_payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('task_id')->nullable();
            $table->bigInteger('client_id')->nullable();
            $table->string('payment_mode')->default('cash')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->double('amount')->nullable();
            $table->date('pay_date')->nullable();
            $table->string('reference_id')->nullable();
            $table->string('notes')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('record_payments');
    }
}
