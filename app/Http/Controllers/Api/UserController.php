<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CentralUser;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //
    public function fetch()
    {
        $user = User::select('id','name', 'email', 'avatar')->get();
        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $user;
        return response()->json($this->response);
    }
}
