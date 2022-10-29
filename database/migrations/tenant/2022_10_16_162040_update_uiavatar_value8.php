<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UpdateUiavatarValue8 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // $users_with_null = DB::table('users')->where('avatar', "")->select('id', 'name')->get();
        // foreach($users_with_null as $user){
        //     DB::table('users')->where(['id' => $user->id, 'avatar' => ""])->update([
        //         'avatar' =>  'https://ui-avatars.com/api/?name='.$user->name
        //     ]);
          
        // }
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
