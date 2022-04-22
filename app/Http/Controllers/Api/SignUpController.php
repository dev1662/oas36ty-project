<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Validator;
use Hash;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Mail;

use App\Models\Tenant;
use App\Models\CentralOrganization;
use App\Models\User;

use App\Mail\SingUpOTP as SingUpOTPMail;

class SignUpController extends Controller
{
    /**
     * @OA\Post(
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
            $this->response["message"] = __('strings.otp_sent_success');
            $this->response["data"] = [
                'email' => $centralOrganization->email,
                'signup_token' => Crypt::encryptString($centralOrganization->id),
            ];
        } else {
            $this->response["message"] = __('strings.otp_sending_failed');
            return response()->json($this->response, 401);
        }
        return response()->json($this->response);
    }

    /**
     * @OA\Post(
     *     tags={"auth"},
     *     path="/signup/verify-email",
     *     operationId="postSignupVerifyEmail",
     *     summary="Signup Verify Email",
     *     description="Signup Verify Email",
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="signup_token", type="string", example="XXXXXXXXXXXXXXXXX"),
     *              @OA\Property(property="email", type="string", example="naveen.w3master@gmail.com"),
     *              @OA\Property(property="otp", type="string", example="123456"),
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

    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'signup_token' => 'required',
            'email' => 'required|email|max:64|exists:App\Models\CentralOrganization,email',
            'otp' => 'required|digits:6',
        ]);
        if($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        try {
            $centralOrganizationID = Crypt::decryptString($request->input('signup_token'));
        } catch (DecryptException $e) {
            $this->response["message"] = __('strings.something_wrong');
            return response()->json($this->response, 401);
        }
        
        $centralOrganization = CentralOrganization::where(['id' => $centralOrganizationID, 'status' => CentralOrganization::STATUS_PENDING])->whereNull('email_verified_at')->first();
        if(!$centralOrganization){
            $this->response["message"] = __('strings.something_wrong');
            return response()->json($this->response, 401);
        }

        if($centralOrganization->otp != $request->input('otp')){
            $this->response["message"] = __('strings.invalid_otp');
            return response()->json($this->response, 401);
        }

        $centralOrganization->email_verified_at = Carbon::now();
        if($centralOrganization->update()){

            $this->response["status"] = true;
            $this->response["message"] = __('strings.email_verified_success');
            $this->response["data"] = [
                'email' => $centralOrganization->email,
                'signup_token' => Crypt::encryptString($centralOrganization->id),
            ];
        } else {
            $this->response["message"] = __('strings.email_verification_failed');
            return response()->json($this->response, 401);
        }
        return response()->json($this->response);
    }
}
