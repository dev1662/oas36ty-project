<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBankDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bank_details', function (Blueprint $table) {
            $table->id();
            $table->string('account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->bigInteger('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('swift_code')->nullable();
            $table->bigInteger('micr_code')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_type')->nullable();
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
        Schema::dropIfExists('bank_details');
    }
}
