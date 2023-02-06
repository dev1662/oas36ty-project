<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB as FacadesDB;

class UpdateAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((Schema::hasColumn('attachments','type'))){
            // Schema::table('attachments', function(Blueprint $table){
            //     $table->string('type')->nullable();
            //    });

            FacadesDB::statement("ALTER TABLE `attachments` CHANGE `type` `type` ENUM('company','task','comment') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;");
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
