<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEmailSignatureTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!(Schema::hasColumn('email_signatures','user_id'))){
            Schema::table('email_signatures', function(Blueprint $table){
                $table->bigInteger('user_id')->nullable();
            });
        }
        if((Schema::hasColumn('email_signatures','singature'))){
            Schema::table('email_signatures', function (Blueprint $table) {
                $table->renameColumn('singature', 'signature');
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
