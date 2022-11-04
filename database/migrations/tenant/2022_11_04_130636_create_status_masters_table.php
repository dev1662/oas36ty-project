<?php

use App\Models\StatusMaster;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateStatusMastersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('status_masters', function (Blueprint $table) {
            $table->id();
            $table->string('type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        DB::table('status_masters')->insert(
            [

                [
                    'type' => 'open'
                ],
                [
                    'type' => 'completed'
                ],
                [
                    'type' => 'invoiced'
                ],
                [
                    'type' => 'closed'
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
        Schema::dropIfExists('status_masters');
    }
}
