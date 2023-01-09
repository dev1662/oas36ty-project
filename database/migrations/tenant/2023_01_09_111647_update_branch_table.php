<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateBranchTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('branches', 'bussiness_name')){
            Schema::table('branches', function (Blueprint $table){
                $table->string('bussiness_name')->nullable();
                $table->string('bussiness_type')->nullable();
                $table->string('gst_number')->nullable();
                $table->string('pan_number')->nullable();
                $table->bigInteger('state_code')->nullable();
                $table->bigInteger('bank_id')->nullable();
                $table->longText('address')->nullable();
                $table->string('website')->nullable();
                $table->string('logo')->nullable();
                $table->bigInteger('mobile')->nullable();
      
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
