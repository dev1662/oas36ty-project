<?php

use App\Models\CentralUser;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if((Schema::hasColumn('users','status'))){
            // Schema::table('user_emails', function(Blueprint $table){
            //     $table->bigInteger('draft_msg_count')->default(0);
            // });

            CentralUser::where('id','!=',null)->where('password','!=',null)->update(['status' => CentralUser::STATUS_ACTIVE]);
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
