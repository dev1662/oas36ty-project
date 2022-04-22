<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;

use App\Models\Tenant;
use App\Models\CentralOrganization;
use App\Models\User;

use App\Mail\SingUpOTP as SingUpOTPMail;

class SignUpController extends Controller
{

    /**
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"auth"},
     *     path="/signup/send-email",
     *     operationId="postSignupSendEmail",
     *     summary="Signup Send Email",
     *     description="Signup Send Email",
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email", type="string", example="naveen.w3master@gmail.com"),
     *          )
     *     ),
     *     @OA\Response(
     *          response=200, 
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Success Message!"),
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
     * )
     */

    public function sendEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:64',
        ]);
        if($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        // Random OTP Generation
        $randomOTP = rand(100000, 999999);

        $centralOrganization = new CentralOrganization($request->all());
        $centralOrganization->otp = $randomOTP;
        $centralOrganization->status = 'pending';
        if($centralOrganization->save()){

            Mail::to($centralOrganization->email)->send(new SingUpOTPMail($randomOTP));
            
            $this->response["status"] = true;
            $this->response["message"] = __('strings.store_success');
            $this->response["data"] = [
                'email' => $centralOrganization->email,
                'signup_token' => Crypt::encryptString($centralOrganization->id),
            ];
        } else {
            $this->response["message"] = __('strings.store_failed');
            return response()->json($this->response, 401);
        }
        return response()->json($this->response);
    }
}
