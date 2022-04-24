<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Auth;
use Hash;
use Carbon\Carbon;

use App\Models\Tenant;
use App\Models\CentralOrganization;
use App\Models\User;

class LoginController extends Controller
{
    /**
     * @OA\Post(
     *     tags={"auth"},
     *     path="/login",
     *     operationId="postUserLogin",
     *     summary="Login",
     *     description="Login",
     *     @OA\Parameter(name="X-Tenant", in="header", required=true, description="Tenant ID"),
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email", type="string", example="naveen.w3master@gmail.com"),
     *              @OA\Property(property="password", type="string", example="password"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=200, 
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Success Message!"),
     *              @OA\Property(
     *                  property="data", 
     *                  type="object",
     *                  @OA\Property(property="token", type="string", example="1|uDm2cTp9fiRYTEopir0X9mdy9CEC20JDPsaAGiiV"),
     *                  @OA\Property(property="name", type="string", example="Naveen"),
     *                  @OA\Property(property="email", type="string", example="naveen.w3master@gmail.com"),
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Validation Error Message!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized Error Message!")
     *          )
     *     ),
     * )
     */

    public function index(Request $request){ 

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:App\Models\User,email',
            'password' => 'required',
        ]);

        if($validator->fails()) {
            $this->response["message"] = $validator->errors()->first();
            return response()->json($this->response, 422);
        }

        $credentials = $request->only('email', 'password');
        // $credentials = array_merge($credentials, ['status' => 'active']);

        $user = User::where("email",$request->email)->first();
        if($user && Hash::check($request->password, $user->password)) {

            $result = array(
                'token' => $user->createToken("Tenant: " . $user->name . " (" . $user->email . ")")->accessToken,
                'name' => $user->name,
                'email' => $user->email,
            );

            $this->response["status"] = true;
            $this->response["message"] = __('strings.login_success');
            $this->response["data"] = $result;
        } else { 
            $this->response["message"] = __('strings.login_failed');
            return response()->json($this->response, 401);
        } 
        return response()->json($this->response);
    }
}
