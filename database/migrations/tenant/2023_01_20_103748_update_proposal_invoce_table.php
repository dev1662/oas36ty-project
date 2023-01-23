<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProposalInvoceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((Schema::hasColumn('invoices','id'))){
            Schema::table('invoices', function(Blueprint $table){
                // $table->dropPrimary();
                // $table->unsignedInteger('id');
                // ALTER TABLE `invoices` CHANGE `id` `id` BIGINT(20) UNSIGNED NOT NULL;
            });
        }
        if(!(Schema::hasColumn('invoices','proposal_id'))){
            Schema::table('invoices', function(Blueprint $table){
                $table->unsignedInteger('proposal_id')->nullable();
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
