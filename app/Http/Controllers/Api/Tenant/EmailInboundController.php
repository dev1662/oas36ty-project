<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailInbound;
use App\Models\EmailMaster;
use Exception;
use Illuminate\Support\Facades\Validator;


class EmailInboundController extends Controller
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
     *     path="/email-inbound",
     *     operationId="getInboundEmails",
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

        $mail = EmailInbound::get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $mail ?? [];
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
     *     path="/email-inbound",
     *     operationId="postEmailInbound",
     *     summary="Create Inbound",
     *     description="Create Inbound",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *               @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *              @OA\Property(
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
     *                         example="ssl / tls / starttls"
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
                'id'=>'required|exists:App\Models\EmailMaster,id|unique:App\Models\EmailInbound,id',
                'mail_transport.option'  => 'required|in:pop,imap',
                'mail_host'       => 'required',
                'mail_port'       => 'required|in:110,995,993,143',
                'mail_username'   => 'required|unique:App\Models\EmailInbound',
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
                'mail_transport'  => $request->input('mail_transport')['option'],
                'mail_host'       => $request->input('mail_host'),
                'mail_port'       => $request->input('mail_port'),
                'mail_username'   => $request->input('mail_username'),
                'mail_password'   => $request->input('mail_password'),
                'mail_encryption' => $request->input('mail_encryption')['option'],
                
            ];
         
    
            // return $data;
           
            $check =  EmailInbound::create($data);
            EmailMaster::where(['id' => $request->input('id')])->update([
                'inbound_status' => 'tick'
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
     *     path="/email-inbound/{emailInboundID}",
     *     operationId="showEmailsInbound",
     *     summary="Inbound Email Details",
     *     description="Inbound Email Details",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="emailInboundID", in="path", required=true, description="emailInbound ID"),
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
        $validator = Validator::make(['emailInbound_id'=>$id], [
            'emailInbound_id' => 'required|exists:App\Models\EmailInbound,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $data = EmailInbound::find($id);

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
     *     path="/email-inbound/{emailInboundID}",
     *     operationId="putEmailInbound",
     *     summary="Update Inbound",
     *     description="Update Inbound",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *  @OA\Parameter(name="emailInboundID", in="path", required=true, description="Email Inbound ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
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
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Updated record successfully"),
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
            $validator =  Validator::make(['emailInbound_id'=>$id] + $request->all(), [
                'emailInbound_id' => 'required|exists:App\Models\EmailInbound,id',
                'mail_transport.option'  => 'required|in:pop,imap',
                'mail_host'       => 'required',
                'mail_port'       => 'required|in:110,995,993,143',
                'mail_username'   => 'sometimes|required|unique:App\Models\EmailInbound'.',id,'.$request->id,
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
                'mail_transport'  => $request->input('mail_transport')['option'],
                'mail_host'       => $request->input('mail_host'),
                'mail_port'       => $request->input('mail_port'),
                'mail_username'   => $request->input('mail_username'),
                'mail_password'   => $request->input('mail_password'),
                'mail_encryption' => $request->input('mail_encryption')['option'],
               
            ];
            
           
            $check =  EmailInbound::where(['id' => $id])->update($data);
    
            if ($check) {
               
                $this->response["status"] = true;
                $this->response["message"] = __('strings.update_success');
                $this->response['data'] = EmailInbound::where(['id' => $id])->first();
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
     *     path="/email-inbound/{emailInboundID}",
     *     operationId="deleteEmailInbound",
     *     summary="Delete Inbound",
     *     description="Delete Inbound",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *  @OA\Parameter(name="emailInboundID", in="path", required=true, description="Email Inbound ID"),
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
        $validator = Validator::make(['emailInbound_id'=>$id], [
            'emailInbound_id' => 'required|exists:App\Models\EmailInbound,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $email_inbound = EmailInbound::find($id);
        if(!$email_inbound){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($email_inbound->delete()) {
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
    //  *     path="/email-inbound-status",
    //  *     operationId="postEmailInboundStatus",
    //  *     summary="Status Inbound",
    //  *     description="Status Inbound",
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
    //  *                  @OA\Property(
    //  *                         property="id",
    //  *                         type="integer",
    //  *                         example="1"
    //  *                      ),
    //  *                      @OA\Property(
    //  *                         property="mail_transport",
    //  *                         type="string",
    //  *                         example="imap"
    //  *                      ),
    //  *                      @OA\Property(
    //  *                         property="mail_host",
    //  *                         type="string",
    //  *                         example="imap.gmail.com"
    //  *                      ),
    //  *                      @OA\Property(
    //  *                         property="mail_port",
    //  *                         type="integer",
    //  *                         example="993"
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
            $validator = Validator::make(['emailInbound_id'=>$request->input('id')], [
                'emailInbound_id' => 'required|exists:App\Models\EmailInbound,id',
               
            ]);
            if ($validator->fails()) {
                $this->response["code"] = "INVALID";
                $this->response["message"] = $validator->errors()->first();
                $this->response["errors"] = $validator->errors();
                return response()->json($this->response, 422);
            }

            EmailInbound::where('id',$request->input('id'))->update(['status'=>'inactive']);
            $check =  EmailInbound::where('mail_username','!=',null)->update(['status'=>'active']);
            if($check){
                $this->response["status"] = true;
                $this->response["message"] = __('strings.update_success');
                $this->response['data'] = EmailInbound::where(['id' => $request->input('id')])->first();
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
