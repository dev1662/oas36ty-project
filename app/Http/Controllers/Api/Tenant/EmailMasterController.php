<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\EmailMaster;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmailMasterController extends Controller
{

    public function __construct() {
        $this->result = new \stdClass();

        //getting method name
        
		$fullroute = \Route::currentRouteAction();
        $method_name = explode('@', $fullroute)[1];

        $methods_arr = ['getEmailCredential'];

        //setting user id which will be accessable for all functions 
        if (in_array($method_name, $methods_arr)) {
            $access_token = apache_request_headers()['X-Tenant'];
            $auth = DB::table('oauth_access_tokens')
                    ->where('id', $access_token)
                    ->orderBy('created_at', 'desc')
                    ->first();
            if ($auth) {
                 $this->user_id = $auth->user_id;
            } else {
                return response()->json([
                            'error' => true,
                            'status_code' => 301,
                            'message' => "Invalid access token",
                            'result' => (object) []
                ]);
            }
        }

    }
    
     /**
     *
     * @OA\post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Mail Setting"},
     *     path="/get-emails",
     *     operationId="getMasterEmails",
     *     summary="Fetch Emails",
     *     description="Fetch Emails",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="search", in="query", required=false, description="Search"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Fetched all records successfully"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="robin@gmail.com"
     *                      ),
     *                      @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         example="inactive / active"
     *                      ),
     *              
     *                       @OA\Property(
     *                         property="created_at",
     *                         type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ),
     *                       @OA\Property(
     *                         property="updated_at",
     *                        type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ),
     *                @OA\Property(
     *                  property="email_inbound",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_transport",
     *                         type="string",
     *                         example="imap"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_host",
     *                         type="string",
     *                         example="imap.gmail.com"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_port",
     *                         type="integer",
     *                         example="993"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_username",
     *                         type="string",
     *                         example="robin@gmail.com"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_password",
     *                         type="string",
     *                         example="igffghjkl"
     *                      ),
     *                       @OA\Property(
     *                         property="mail_encryption",
     *                         type="string",
     *                         example="robin@gmail.com"
     *                      ),
     *                      @OA\Property(
     *                         property="status",
     *                         type="enum",
     *                         example="inactive"
     *                      ),
     *                       @OA\Property(
     *                         property="created_at",
     *                         type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ),
     *                       @OA\Property(
     *                         property="updated_at",
     *                        type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ),
     *
     *                  ),
     *                  ),
     *             @OA\Property(
     *                  property="email_outbound",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_transport",
     *                         type="string",
     *                         example="smtp"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_host",
     *                         type="string",
     *                         example="smtp.gmail.com"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_port",
     *                         type="integer",
     *                         example="465"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_username",
     *                         type="string",
     *                         example="robin@gmail.com"
     *                      ),
     *                      @OA\Property(
     *                         property="mail_password",
     *                         type="string",
     *                         example="igffghjkl"
     *                      ),
     *                       @OA\Property(
     *                         property="mail_encryption",
     *                         type="string",
     *                         example="robin@gmail.com"
     *                      ),
     *                      @OA\Property(
     *                         property="status",
     *                         type="enum",
     *                         example="inactive"
     *                      ),
     *                       @OA\Property(
     *                         property="created_at",
     *                         type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ),
     *                       @OA\Property(
     *                         property="updated_at",
     *                        type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ),
     *
     *                  ),
     *              ),
     *                ),
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *          response=422,
     *          description="Validation Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=false),
     *              @OA\Property(property="message", type="string", example="Something went wrong!")
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized access!")
     *          )
     *     ),
     * )
     */

    public function getEmailCredential(Request $request){

    $dbname = $request->header('X-Tenant'); //json_decode($request->header('currrent'))->tenant->organization->name;
    $dbname = config('tenancy.database.prefix').strtolower($dbname);
    // return   $dbname;
    $this->switchingDB($dbname);

    $details_arr = EmailMaster::with(['emailInbound','emailOutbound'])->get();

    $this->response["status"] = true;
    $this->response["message"] = __('strings.get_all_success');
    $this->response["data"] = $details_arr ?? [];
    return response()->json($this->response);

    }

        /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Mail Setting"},
     *     path="/store-email",
     *     operationId="postStoreEmail",
     *     summary="Add new Email",
     *     description="Add new Email",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="robin@gmail.com"
     *                      ),
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Created new record successfully"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                   @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="robin@gmail.com"
     *                      ),
     *                      @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         example="active / inactive"
     *                      ),
     *                     
     *              ),
     *          )
     *     ),
     *     @OA\Response(
     *          response=401,
     *          description="Unauthorized Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Unauthorized access!")
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
     *                  property="email",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected email is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */

    public function storeMail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'   => 'required|unique:App\Models\EmailMaster',
        ]);

        if($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // Random OTP Generation
       $update = EmailMaster::create(['email'=>$request->email]);

        if($update){

            $this->response["status"] = true;
            $this->response["message"] = __('strings.store_success');
            $this->response["data"] = $update;
        } else {
            $this->response["message"] = 'Something went wrong !!!';
            return response()->json($this->response, 401);
        }
        return response()->json($this->response,200);
    }



}
