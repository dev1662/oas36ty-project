<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserAccessPrivilegesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_access_privileges', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_access_master_id')->nullable();
            $table->bigInteger('user_role_id')->nullable();
            $table->bigInteger('all_master_id')->nullable();
            $table->bigInteger('privilege_id')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');           
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_access_privileges');
    }
}
