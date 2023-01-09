<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name', 64)->nullable();
            $table->string('bussiness_name')->nullable();
            $table->bigInteger('bussiness_type')->nullable();
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->bigInteger('state_code')->nullable();
            $table->bigInteger('mobile')->nullable();
            $table->bigInteger('bank_id')->nullable();
            $table->longText('address')->nullable();
            $table->string('website')->nullable();
            $table->string('logo')->nullable();
            $table->enum('type', ['delete', 'dont_delete'])->default('delete');
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
        Schema::dropIfExists('branches');
    }
}
