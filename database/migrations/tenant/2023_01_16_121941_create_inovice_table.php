<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInoviceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->nullable();
            $table->string('client_gst_number')->nullable();
            $table->integer('state_code')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('notes')->nullable();
            $table->string('item_details')->nullable();
            $table->double('amount')->nullable();
            $table->double('discount')->nullable();
            $table->double('taxable_amt')->nullable();
            $table->double('igst')->nullable();
            $table->double('igst_amt')->nullable();
            $table->double('sgst')->nullable();
            $table->double('sgst_amt')->nullable();
            $table->double('cgst')->nullable();
            $table->double('cgst_amt')->nullable();
            $table->double('utgst')->nullable();
            $table->double('utgst_amt')->nullable();
            $table->double('sub_total')->nullable();
            $table->double('pocket_expenses')->nullable();
            $table->string('expenses_details')->nullable();
            $table->double('adjustment_amt')->nullable();
            $table->double('total_amt')->nullable();
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
        Schema::dropIfExists('invoices');
    }
}
