<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\EmailsSetting;
use Illuminate\Http\Request;
use App\Models\EmailOutbound;
use App\Models\User;
use App\Models\UserEmail;
use Exception;
use Illuminate\Support\Facades\Validator;


class EmailOutboundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     * 
     * 
     */


     /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Mail Setting"},
     *     path="/email-outbound",
     *     operationId="getEmails",
     *     summary="Email settings",
     *     description="Email settings",
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

    public function index(Request $request)
    {
        $dbname = $request->header('X-Tenant'); //json_decode($request->header('currrent'))->tenant->organization->name;
        $dbname = config('tenancy.database.prefix').strtolower($dbname);
        // return   $dbname;
        $this->switchingDB($dbname);

        $mail = EmailOutbound::get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $mail ?? [];
        return response()->json($this->response);
    }

    public function fetchEmails_outbound( Request $request)
    {
        $dbname = $request->header('X-Tenant'); //json_decode($request->header('currrent'))->tenant->organization->name;
        $dbname = config('tenancy.database.prefix').strtolower($dbname);
        // return   $dbname;
        $this->switchingDB($dbname);
        $user = $request->user();
        $user_emails= UserEmail::where('user_id', $user->id)->get();
        $outbound_mail = [];
        // $count = 0;
        foreach($user_emails as $index=> $user_email){
           $emailSetting =  EmailsSetting::where(['id'=> $user_email->emails_setting_id, 'outBound_status' => 'tick'])->first();
           if($emailSetting){
            $outarr = EmailOutbound::where('id', $user_email->emails_setting_id)->select('id','mail_username')->first();
            $user_avatar = User::where('id', $user->id)->select('avatar', 'name')->first();
           $outbound_mail[] = ['email' => $outarr->mail_username, 'id' => $outarr->id];

           }
        //    $count++;
        }
        $this->response['status'] = true;
        $this->response['message'] = 'out bound emails fetched';
        $this->response['data'] = $outbound_mail;
        return response()->json($this->response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

     /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Mail Setting"},
     *     path="/email-outbound",
     *     operationId="postEmailOutbound",
     *     summary="Create Outbound",
     *     description="Create Outbound",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *              @OA\Property(
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
    public function store(Request $request)
    {
        try{
            $validator =  Validator::make(request()->all(), [
                'id'=>'required|exists:App\Models\EmailsSetting,id|unique:App\Models\EmailOutbound,id',
                'mail_transport'  => 'required| ',
                'mail_host'       => 'required',
                'mail_port'       => 'required|in:25,465,587,2525',
                'mail_username'   => 'required|unique:App\Models\EmailOutbound',
                'mail_password'   => 'required',
                'mail_encryption.option' => 'required|in:tls,ssl,starttls',
                
            ]
        );
           
            if ($validator->fails()) {
                $this->response["code"] = "INVALID";
                $this->response["message"] = $validator->errors()->first();
                $this->response["errors"] = $validator->errors();
                return response()->json($this->response, 422);
            }
            $data = [
                'id'=>$request->input('id'),
                'mail_transport'  => $request->input('mail_transport'),
                'mail_host'       => $request->input('mail_host'),
                'mail_port'       => $request->input('mail_port'),
                'mail_username'   => $request->input('mail_username'),
                'mail_password'   => $request->input('mail_password'),
                'mail_encryption' => $request->input('mail_encryption')['option'],
               
            ];
          
        
            $check =  EmailOutbound::create($data);
            EmailsSetting::where(['id' => $request->input('id')])->update([
                'outbound_status' => 'tick'
            ]);
            if ($check) {
                $this->response["status"] = true;
                $this->response["message"] = __('strings.store_success');
                $this->response['data'] = $check;
                return response()->json($this->response);
            } else {
                return response()->json('Something went wrong !!!');
            }
    
        }catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
    ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     //
    // }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     
     /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Mail Setting"},
     *     path="/email-outbound/{emailOutboundID}",
     *     operationId="showEmails",
     *     summary="Outbound Email Details",
     *     description="Outbound Email Details",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="emailOutboundID", in="path", required=true, description="emailOutbound ID"),
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
    public function show($id)
    {
        $validator = Validator::make(['emailOutbound_id'=>$id], [
            'emailOutbound_id' => 'required|exists:App\Models\EmailOutbound,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $data = EmailOutbound::find($id);

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $data;
        return response()->json($this->response);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

      /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"Mail Setting"},
     *     path="/email-outbound/{emailOutboundID}",
     *     operationId="putEmailOutbound",
     *     summary="Update Outbound",
     *     description="Update Outbound",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *  @OA\Parameter(name="emailOutboundID", in="path", required=true, description="Email Outbound ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
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
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Record updated successfully"),
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                   @OA\Property(
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

    public function update(Request $request, $id)
    {
        try{
           // return $id;
            $validator =  Validator::make(['emailOutbound_id'=>$id] + $request->all(), [
                'emailOutbound_id' => 'required|exists:App\Models\EmailOutbound,id',
                'mail_transport'  => 'required| ',
                'mail_host'       => 'required',
                'mail_port'       => 'required|in:25,465,587,2525',
                'mail_username'   => 'sometimes|required|unique:App\Models\EmailOutbound'.',id,'.$request->id,
                'mail_password'   => 'required',
                'mail_encryption.option' => 'required|in:tls,ssl,starttls',
               
            ]
        );
           
            if ($validator->fails()) {
                $this->response["code"] = "INVALID";
                $this->response["message"] = $validator->errors()->first();
                $this->response["errors"] = $validator->errors();
                return response()->json($this->response, 422);
            }
            $data = [
               // 'id'=> $request->input('id'),
                'mail_transport'  => $request->input('mail_transport'),
                'mail_host'       => $request->input('mail_host'),
                'mail_port'       => $request->input('mail_port'),
                'mail_username'   => $request->input('mail_username'),
                'mail_password'   => $request->input('mail_password'),
                'mail_encryption' => $request->input('mail_encryption')['option'],
               
            ];
            
           
            $check =  EmailOutbound::where(['id' => $id])->update($data);
    
            if ($check) {
               
                $this->response["status"] = true;
                $this->response["message"] = __('strings.update_success');
                $this->response['data'] = EmailOutbound::where(['id' => $id])->first();
                return response()->json($this->response);
    
            } else {
                return response()->json('Something went wrong !!!');
            }
    
        }catch (Exception $ex) {
            return response()->json([
                'error' => $this->error,
                'status_code' => $ex->getCode(),
                'message' => $ex->getMessage(),
                'result' => $this->result
    ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
 /**
     *
     * @OA\Delete(
     *    security={{"bearerAuth":{}}},
     *     tags={"Mail Setting"},
     *     path="/email-outbound/{emailOutboundID}",
     *     operationId="deleteEmailOutbound",
     *     summary="Delete Outbound",
     *     description="Delete Outbound",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *  @OA\Parameter(name="emailOutboundID", in="path", required=true, description="Email Outbound ID"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Deleted successfully!"),
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
     *          response=403,
     *          description="Forbidden Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Forbidden!")
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
     *                  property="branch_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected branch_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */


    public function destroy($id)
    {
        try{
        $validator = Validator::make(['emailOutbound_id'=>$id], [
            'emailOutbound_id' => 'required|exists:App\Models\EmailOutbound,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $email_outbound = EmailOutbound::find($id);
        if(!$email_outbound){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($email_outbound->delete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }catch (Exception $ex) {
        return response()->json([
            'error' => $this->error,
            'status_code' => $ex->getCode(),
            'message' => $ex->getMessage(),
            'result' => $this->result
      ]);
    }

    }

  
    // /**
    //  *
    //  * @OA\Post(
    //  *     security={{"bearerAuth":{}}},
    //  *     tags={"Mail Setting"},
    //  *     path="/email-outbound-status",
    //  *     operationId="postEmailOutboundStatus",
    //  *     summary="Status Outbound",
    //  *     description="Status Outbound",
    //  *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
    //  * 
    //  *     @OA\RequestBody(
    //  *          required=true,
    //  *          @OA\JsonContent(
    //  *             type="object",
    //  *              @OA\Property(
    //  *                         property="id",
    //  *                         type="integer",
    //  *                         example="1"
    //  *                      ),
    //  *         )
    //  *     ),
    //  *     @OA\Response(
    //  *          response=200,
    //  *          description="Successful Response",
    //  *          @OA\JsonContent(
    //  *              @OA\Property(property="status", type="boolean", example=true),
    //  *              @OA\Property(property="message", type="string", example="Record updated successfully"),
    //  *              @OA\Property(
    //  *                  property="data",
    //  *                  type="object",
    //  *                   @OA\Property(
    //  *                         property="id",
    //  *                         type="integer",
    //  *                         example="1"
    //  *                      ),
    //  *                      @OA\Property(
    //  *                         property="mail_transport",
    //  *                         type="string",
    //  *                         example="smtp"
    //  *                      ),
    //  *                      @OA\Property(
    //  *                         property="mail_host",
    //  *                         type="string",
    //  *                         example="smtp.gmail.com"
    //  *                      ),
    //  *                      @OA\Property(
    //  *                         property="mail_port",
    //  *                         type="integer",
    //  *                         example="465"
    //  *                      ),
    //  *                      @OA\Property(
    //  *                         property="mail_username",
    //  *                         type="string",
    //  *                         example="robin@gmail.com"
    //  *                      ),
    //  *                      @OA\Property(
    //  *                         property="mail_password",
    //  *                         type="string",
    //  *                         example="igffghjkl"
    //  *                      ),
    //  *                       @OA\Property(
    //  *                         property="mail_encryption",
    //  *                         type="string",
    //  *                         example="robin@gmail.com"
    //  *                      ),
    //  *                      @OA\Property(
    //  *                         property="status",
    //  *                         type="enum",
    //  *                         example="inactive"
    //  *                      ),
    //  *                       @OA\Property(
    //  *                         property="created_at",
    //  *                         type="timestamp",
    //  *                         example="2022-09-02T06:01:37.000000Z"
    //  *                      ),
    //  *                       @OA\Property(
    //  *                         property="updated_at",
    //  *                        type="timestamp",
    //  *                         example="2022-09-02T06:01:37.000000Z"
    //  *                      ),
    //  *              ),
    //  *          )
    //  *     ),
    //  *     @OA\Response(
    //  *          response=401,
    //  *          description="Unauthorized Response",
    //  *          @OA\JsonContent(
    //  *              @OA\Property(property="message", type="string", example="Unauthorized access!")
    //  *          )
    //  *     ),
    //  *     @OA\Response(
    //  *          response=422,
    //  *          description="Validation Response",
    //  *          @OA\JsonContent(
    //  *              @OA\Property(property="status", type="boolean", example=false),
    //  *              @OA\Property(property="message", type="string", example="Something went wrong!"),
    //  *              @OA\Property(property="code", type="string", example="INVALID"),
    //  *              @OA\Property(
    //  *                  property="errors",
    //  *                  type="object",
    //  *                      @OA\Property(
    //  *                  property="email",
    //  *                  type="array",
    //  *                  @OA\Items(
    //  *                         type="string",
    //  *                         example="The selected email is invalid."
    //  *                  ),
    //  *              ),
    //  *                  ),
    //  *              ),
    //  *          )
    //  *     ),
    //  * )
    //  */

    public function update_active_inactive_status(Request $request){
        try{
            $validator = Validator::make(['emailOutbound_id'=>$request->input('id')], [
                'emailOutbound_id' => 'required|exists:App\Models\EmailOutbound,id',
               
            ]);
            if ($validator->fails()) {
                $this->response["code"] = "INVALID";
                $this->response["message"] = $validator->errors()->first();
                $this->response["errors"] = $validator->errors();
                return response()->json($this->response, 422);
            }

            EmailOutbound::where('id',$request->input('id'))->update(['status'=>'inactive']);
            $check =  EmailOutbound::where('mail_username','!=',null)->update(['status'=>'active']);
            if($check){
                $this->response["status"] = true;
                $this->response["message"] = __('strings.update_success');
                $this->response['data'] = EmailOutbound::where(['id' => $request->input('id')])->first();
                return response()->json($this->response);
            }
    

        }catch (Exception $ex) {
        return response()->json([
            'error' => $this->error,
            'status_code' => $ex->getCode(),
            'message' => $ex->getMessage(),
            'result' => $this->result
      ]);
    }

    }

}
