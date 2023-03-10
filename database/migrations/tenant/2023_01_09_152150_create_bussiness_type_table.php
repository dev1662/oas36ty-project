<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateBussinessTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bussiness_type', function (Blueprint $table) {
            $table->id();
            $table->string('bussiness_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('bussiness_type')->insert(
            [

                [
                    'bussiness_type' => 'Subsidiary Company'
                ],
                [
                    'bussiness_type' => 'Project Office'
                ],
                [
                    'bussiness_type' => 'Branch Office'
                ],
                [
                    'bussiness_type' => 'Liaison Office'
                ],
                [
                    'bussiness_type' => 'Limited Liability Partnership(LLP)'
                ],
                [
                    'bussiness_type' => 'Cooperatives'
                ],
                [
                    'bussiness_type' => 'Partnership'
                ],
                [
                    'bussiness_type' => 'Joint Hindu Family business'
                ],
                [
                    'bussiness_type' => 'Sole proprietorship'
                ],
                [
                    'bussiness_type' => 'Unlimited Company'
                ],
            
            ]
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bussiness_type');
    }
}
