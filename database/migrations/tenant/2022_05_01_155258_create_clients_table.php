<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('name', 64);
            $table->enum('type', ['delete', 'dont_delete'])->default('delete');
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->string('gst_number')->nullable();
            $table->integer('state_code')->nullable();
            $table->string('pan')->nullable();
            $table->string('tan')->nullable();
            $table->bigInteger('client_types')->nullable();
            $table->double('annual_turn_over')->nullable();
            $table->double('opening_balance')->nullable();
            $table->date('opening_bal_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            // $table->enum('status', ['active', 'inactive'])->default('inactive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
