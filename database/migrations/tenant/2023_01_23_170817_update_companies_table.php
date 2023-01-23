<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!(Schema::hasColumn('companies','location'))){
            Schema::table('companies', function(Blueprint $table){
                $table->string('location')->nullable();
                $table->string('address')->nullable();
                $table->string('gst_number')->nullable();
                $table->integer('state_code')->nullable();
                $table->string('pan')->nullable();
                $table->bigInteger('client_types')->nullable();
                $table->double('annual_turn_over')->nullable();
                $table->double('opening_balance')->nullable();
                $table->date('opening_bal_date')->nullable();
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
