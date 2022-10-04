<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameClientsToComapnies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::rename('clients', 'companies');
        // Schema::table('tasks', function (Blueprint $table) {
        //     $table->renameColumn('client_id', 'company_id');
        // });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comapnies', function (Blueprint $table) {
            //
        });
    }
}
