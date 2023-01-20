<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRecordPaymentInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('record_payment_invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('record_payment_id')->nullable();
            $table->bigInteger('invoice_id')->nullable();
            $table->double('tds_deducted')->nullable();
            $table->double('paid_amount')->nullable();
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
        Schema::dropIfExists('record_payment_invoices');
    }
}
