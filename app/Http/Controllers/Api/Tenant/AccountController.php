<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Tenant;
use App\Models\CentralUser;
use App\Models\User;

use App\Http\Resources\TenantResource;
use App\Http\Resources\OrganizationResource;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session as FacadesSession;
use Illuminate\Support\Facades\Validator as FacadesValidator;

class AccountController extends Controller
{
    /**
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"auth"},
     *     path="/logout",
     *     operationId="postLogout",
     *     summary="Logout",
     *     description="Logout",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Response(
     *          response=200, 
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Logout successfully"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Something went wrong!")
     *          )
     *     ),
     * )
     */

    public function logout(Request $request){ 

        $user = $request->user();
        // return $user;
        // Revoke Token
        // return FacadesSession::all();
        $user->token()->revoke();
        

        $this->response["status"] = true;
        $this->response["message"] = __('strings.logged_out');
        return response()->json($this->response);
    }
    public function update_profile_picture(Request $req)
    {
        // $validator = FacadesValidator::make($req->all(),[
        //     'user_id' => 'required'
        // ]);
        // if ($validator->fails()) {
        //     $this->response["code"] = "INVALID";
        //     $this->response["message"] = $validator->errors()->first();
        //     $this->response["errors"] = $validator->errors();
        //     return response()->json($this->response, 422);
        // }
        $user = $req->user();
        if($req->url){
            User::where('id', $user->id)->update([
                'avatar' => $req->url
            ]);
            $this->response['message'] = 'Profile Updated!';
            $this->response['status'] = true;
            return response()->json($this->response);
        }else{
            $this->response['message'] = 'Image is required';
            $this->response['status'] = false;
            return response()->json($this->response, 201);
        }
    }
    public function update_password(Request $req)
    {
        $user = $req->user();
        if($req->c_password){
            CentralUser::where('id', $user->id)->update([
                'password' => Hash::make($req->c_password)
            ]);
            User::where('id', $user->id)->update([
                'password' => Hash::make($req->c_password)
            ]);
            $this->response['message'] = 'Profile Updated!';
            $this->response['status'] = true;
            return response()->json($this->response);
        }else{
            $this->response['message'] = 'password field is required';
            $this->response['status'] = false;
            return response()->json($this->response,201);
        }
    }
}
