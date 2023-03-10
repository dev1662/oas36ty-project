<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\DB;
use Hash;
// use Validator;

use App\Models\CentralUser;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash as FacadesHash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PDO;

class ResetPasswordController extends Controller
{
   
    /**
     * @OA\Post(
     *     tags={"auth"},
     *     path="/reset-password",
     *     operationId="postResetPassword",
     *     summary="Reset Password",
     *     description="Reset Password",
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="token", type="string", example="XXXXXXXXXXXXXXXXXXXXXXXXXX"),
     *              @OA\Property(property="email", type="string", example="naveen.w3master@gmail.com"),
     *              @OA\Property(property="password", type="string", example="password"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=200, 
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Password updated successfully"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Something went wrong!"),
     *              @OA\Property(property="code", type="string", example="INVALID"),
     *              @OA\Property(
     *                  property="errors", 
     *                  type="object",
     *                      @OA\Property(
     *                  property="token", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected token is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:15',
        ]);

        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
    
        
        $status = Password::broker('central_users')->reset(
            $request->only('email', 'password', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => FacadesHash::make($password)
                ]);
                // return "hh";
                
                $user->save();
                
                event(new PasswordReset($user));
            }
        );
        // if(Str::length($request->token) === 64){
            
        //     $status = Password::broker('central_users')->reset(
        //         $request->only('email', 'password', 'token'),
        //         function ($user, $password) {
        //             $user->forceFill([
        //                 'password' => FacadesHash::make($password)
        //             ]);
        //             // return $user;
                    
        //             $user->save();
                    
        //             event(new PasswordReset($user));
        //         }
        //     );
        // }
        // if(Str::length($request->token) > 64){
        //     $tok= Crypt::decryptString($request->token);
        //     return $tok;
        //     $status = Password::broker('central_users')->reset(
        //         $request->only('email', 'password'),
        //         function ($user, $password) {
        //             $user->forceFill([
        //                 'password' => FacadesHash::make($password)
        //             ]);
        //             // return $user;
                    
        //             $user->save();
                    
        //             event(new PasswordReset($user));
        //         }
        //     );
        // }
       
        
        if ($status === Password::PASSWORD_RESET) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.reset_password_success');
            return response()->json($this->response);
        }

        // $tokenData = DB::table('password_resets')->where('token', $request->token)->first();
        // if($tokenData){
        //     $centralUser = CentralUser::where('email', $tokenData->email)->first();
        //     if (!$centralUser){
        //         $this->response["message"] = __('strings.reset_password_failed');
        //         return response()->json($this->response);
        //     }
        //     $centralUser->password = Hash::make($request->password);
        //     $centralUser->update();
        //     DB::table('password_resets')->where('email', $centralUser->email)->delete();

        //     $this->response["status"] = true;
        //     $this->response["message"] = __('strings.reset_password_success');
        //     return response()->json($this->response);
        // }
        
        $this->response["message"] = __('strings.reset_password_failed');
        return response()->json($this->response);
    }
    public function setPassword(Request $request)
    {
        // return "h";
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            // 'email' => 'required|email',
            'password' => 'required|string|min:6|max:15',
        ]);
        
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // return "hh";
        try{
            
          $tokenData =  json_decode(Crypt::decryptString($request->token));
        }catch(DecryptException $ex){
            return $ex->getMessage();
        }
  
        $hash_password = FacadesHash::make($request->password);
        // return FacadesHash::make($request->password);
        if(CentralUser::where('email', $tokenData->email) && User::where('email', $tokenData->email)){

            // $token = Crypt::decryptString($request->token);
            // $centralUser = tenancy()->central(function ($tenant) use($request) {
            $centralUser = CentralUser::where('email', $tokenData->email)->update(
                [
                    'password' => $hash_password,
                    'status' => 'active'
                ],
              
            );
            // return $tenant;
            // $centralUser->tenants()->attach($tenant);
            // return $centralUser;
        // });
        // return $centralUser;
        // $tokenData = json_decode(Crypt::decryptString($request->token));
        $mainUser = CentralUser::where('email', $tokenData->email)->first();
        $tenant = $mainUser->tenants()->find($tokenData->tenant_id);
        $user = $tenant->run(function ($tenant) use ($mainUser) {
        return $user = User::where('email', $mainUser->email)->update([
            'password' => $mainUser->password,
            'status' => 'active'
        ]);
    });
    }
        

        if ($user) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.reset_password_success');
            return response()->json($this->response);
        }
        $this->response["message"] = __('strings.reset_password_failed');
        return response()->json($this->response);
    }
}
