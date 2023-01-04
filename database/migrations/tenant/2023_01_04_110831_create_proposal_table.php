<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProposalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposal', function (Blueprint $table) {
            $table->id();
            $table->date('proposal_date')->nullable();
            $table->string('client_name')->nullable();
            $table->string('concerned_person')->nullable();
            $table->longText('address')->nullable();
            $table->longText('subject')->nullable();
            $table->longText('prephase')->nullable();
            $table->longText('internal_notes')->nullable();
            $table->longText('footer_title')->nullable();
            $table->longText('footer_description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposal');
    }
}
