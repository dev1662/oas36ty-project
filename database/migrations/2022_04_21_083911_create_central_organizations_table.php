<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCentralOrganizationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('central_organizations', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->string('name', 255);
            $table->string('email', 64);
            $table->timestamps();
            $table->softDeletes();
            $table->enum('status', ['active', 'inactive', 'blocked'])->default('inactive');
            
            $table->foreign('tenant_id')->references('id')->on('tenants')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('central_organizations');
    }
}
