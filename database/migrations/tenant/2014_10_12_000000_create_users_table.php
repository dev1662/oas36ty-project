<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('global_id')->unique();
            $table->string('name', 64);
            $table->string('display_name', 64)->nullable();
            $table->string('avatar')->nullable();
            $table->string('email', 64)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('phone')->nullable();
            $table->string('location')->nullable();
            $table->bigInteger('user_role_id')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->enum('status', ['pending', 'active', 'inactive', 'declined'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
