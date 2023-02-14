<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateBusinessTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasColumn('bussiness_type','bussiness_type')){
        DB::table('bussiness_type')->truncate();
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
