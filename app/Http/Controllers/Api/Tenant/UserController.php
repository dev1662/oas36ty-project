<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Jobs\TestQueueRecieveEmail;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Mail;

use App\Models\CentralUser;
use App\Models\User;

use App\Mail\JoiningInvitation as JoiningInvitationMail;
use App\Models\Branch;
use App\Models\EmailsSetting;
use App\Models\Mailbox;
use App\Models\UserEmail;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use PDO;
use Symfony\Component\HttpFoundation\File\File as FileFile;

class UserController extends Controller
{
    // public function emails_recieved(Request $request)
    // { 
    //     $centralUser =  CentralUser::where('email',json_decode($request->header('currrent'))->email)->first();

    //     $tenant = $centralUser->tenants()->find($request->header('X-Tenant'));
    //     tenancy()->initialize($tenant);
    //     // $users_emails = UserEmail::with(['users', 'EmailsSetting'])->get();
    //     // return [
    //     //     // "tenant" => $request->header('X-Tenant'),
    //     //     "user_emails" => $users_emails,
    //     //     "user" => $centralUser
    //     //     // "email_settings" => EmailsSetting::with(['emailInbound','emailOutbound'])->get(),
            
    //     // ];
    //     // $tenant = $request->header('X-Tenant');
    //     // $this->switchingDB('oas36ty_org_'.$tenant);
    //     $data = [
    //         'mail_host' => "imap.gmail.com",
    //         'mail_transport' => "imap",
    //         'mail_encryption' => "ssl",
    //         'mail_username' => "jakeraubin@gmail.com",
    //         'mail_password' => "yfkfaxbeignwfebw",
    //         'mail_port' => 993,
            
    //     ];
        
    //     $host = '{'.$data['mail_host'].':'.$data['mail_port'].'/'.$data['mail_transport'].'/'.$data['mail_encryption'].'}';
    //     // return $host;
    //     // / Your gmail credentials /
    //     $user = $data['mail_username'];
    //     $password = $data['mail_password'];
        
    //     // / Establish a IMAP connection /
    //     $conn = imap_open($host, $user, $password)
        
    //     or die('unable to connect Gmail: ' . imap_last_error());
    //     $mails = imap_search($conn, 'ALL');
    //     // / loop through each email id mails are available. /
    //     if ($mails) {
    //         rsort($mails);
    //         // / For each email /
    //         foreach ($mails as $email_number) {
    //             $headers = imap_fetch_overview($conn, $email_number, 0);
        
    //             // $structure = imap_fetchstructure($conn, $email_number);
        
    //             // $attachments = array();
        
    //             // /* if any attachments found... */
    //             // if(isset($structure->parts) && count($structure->parts)) 
    //             // {
    //             //     for($i = 0; $i < count($structure->parts); $i++) 
    //             //     {
    //             //         $attachments[$i] = array(
    //             //             'is_attachment' => false,
    //             //             'filename' => '',
    //             //             'name' => '',
    //             //             'attachment' => ''
    //             //         );
        
    //             //         if($structure->parts[$i]->ifdparameters) 
    //             //         {
    //             //             foreach($structure->parts[$i]->dparameters as $object) 
    //             //             {
    //             //                 if(strtolower($object->attribute) == 'filename') 
    //             //                 {
    //             //                     $attachments[$i]['is_attachment'] = true;
    //             //                     $attachments[$i]['filename'] = $object->value;
    //             //                 }
    //             //             }
    //             //         }
        
    //             //         if($structure->parts[$i]->ifparameters) 
    //             //         {
    //             //             foreach($structure->parts[$i]->parameters as $object) 
    //             //             {
    //             //                 if(strtolower($object->attribute) == 'name') 
    //             //                 {
    //             //                     $attachments[$i]['is_attachment'] = true;
    //             //                     $attachments[$i]['name'] = $object->value;
    //             //                 }
    //             //             }
    //             //         }
        
    //             //         if($attachments[$i]['is_attachment']) 
    //             //         {
    //             //             $attachments[$i]['attachment'] = imap_fetchbody($conn, $email_number, $i+1);
        
    //             //             /* 3 = BASE64 encoding */
    //             //             if($structure->parts[$i]->encoding == 3) 
    //             //             { 
    //             //                 $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
    //             //             }
    //             //             /* 4 = QUOTED-PRINTABLE encoding */
    //             //             elseif($structure->parts[$i]->encoding == 4) 
    //             //             { 
    //             //                 $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
    //             //             }
    //             //         }
    //             //     }
    //             // }
    //             // Log::info($attachments);
        
    //             // Log::info($headers);
    //             $message = imap_fetchbody($conn, $email_number, '1');
    //             $subMessage = substr($message, 0, 150);
    //             $finalMessage = trim(quoted_printable_decode($subMessage));
    //             // Log::info($finalMessage);die;
    //             $details_of_email = [];
    //             foreach($headers as $index => $header){
    //                 $details_of_email[$index] =[
    //                     'subject' => $header->subject,
    //                     'from_name' => $header->from,
    //                     'from_email' => $header->from,
    //                     'message_id' => $header->message_id,
    //                     'to_email' => $header->to,
    //                     'message' => $finalMessage,
    //                     'date' => $header->date,
    //                     'u_date' => $header->udate,
        
    //                 ];
                    
    //                $insert= Mailbox::create($details_of_email[$index]);
                   
    //             }
                
    //             // return;
    //         }// End foreach
    //         if($insert){
    //             return "success";
    //         }
        
    //     }//endif
        
  
    //     imap_close($conn);
        

    //     // $request_email = json_decode($request->header('currrent'))->email;
    //     // $centralUser = CentralUser::where('email', $request_email)->first();
    //     // $tenant = $centralUser->tenants()->find($request->header('X-Tenant'));
    //     // tenancy()->initialize($tenant);
    //     // return $request_email;die;
       
    //         // Artisan::call('queue:listen');
    // }
    public function get_emails_to_assign(Request $request)
    {

        $dbname = $request->header('X-Tenant'); //json_decode($request->header('currrent'))->tenant->organization->name;
        $dbname = config('tenancy.database.prefix') . strtolower($dbname);
        // return   $dbname;
        $this->switchingDB($dbname);

        $details_arr = EmailsSetting::with(['emailInbound', 'emailOutbound'])->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $details_arr ?? [];
        return response()->json($this->response);
    }
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"users"},
     *     path="/users",
     *     operationId="getUsers",
     *     summary="Users",
     *     description="Users",
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
     *                         property="name",
     *                         type="string",
     *                         example="Naveen"
     *                      ),
     *                      @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="naveen.w3master@gmail.com"
     *                      ),
     *                      @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         example="active"
     *                      ),
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
        $dbname = $request->header('X-Tenant');
        $dbname = config('tenancy.database.prefix') . strtolower($dbname);

        $this->switchingDB($dbname);
        // return 'hh';
        $validator = Validator::make($request->all(), [
            'search' => 'nullable',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $search = $request->search;
        // tenantdevCentrik

        // $path = 'http://localhost/oas36ty/local_api/storage/tenant'.$request->header('X-Tenant').'/';

        // return $path;
        $users = User::select('id', 'name', 'avatar', 'email', 'status')->where(function ($q) use ($search) {
            if ($search) $q->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%');
        })->latest()->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] =
         ["users" => $users,
        //  "path" => $path
        ];
        // $request_email = json_decode($request->header('currrent'))->email;

        // $email_master = EmailsSetting::where('email', $request_email)->with(['emailInbound', 'emailOutbound'])->first();
        // $data = [
        //     'mail_host' => $email_master->emailInbound->mail_host,
        //     'mail_transport' => $email_master->emailInbound->mail_transport,
        //     'mail_encryption' => $email_master->emailInbound->mail_encryption,
        //     'mail_username' => $email_master->emailInbound->mail_username,
        //     'mail_password' => $email_master->emailInbound->mail_password,
        //     'mail_port' => $email_master->emailInbound->mail_port,
            
        // ];

        //  $data = [
        //     'mail_host' => "imap.gmail.com",
        //     'mail_transport' => "imap",
        //     'mail_encryption' => "ssl",
        //     'mail_username' => " jakeraubin@gmail.com",
        //     'mail_password' => "yfkfaxbeignwfebw",
        //     'mail_port' => 993,
            
        // ];
        // $job= TestQueueRecieveEmail::dispatchAfterResponse($data);
        // Log::info($job);
        // dispatch(new TestQueueRecieveEmail($data))->afterResponse();
            // Artisan::call('queue:listen');
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"users"},
     *     path="/users",
     *     operationId="postUsers",
     *     summary="Create User",
     *     description="Create User",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Naveen", description=""),
     *             @OA\Property(property="email", type="string", example="naveen.w3master@gmail.com", description=""),
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
     *                  @OA\Property(property="id", type="string", example="1"),
     *                  @OA\Property(property="name", type="string", example="Hyderabad Contacts", description=""),
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
        // return 'h';

        $validator = Validator::make($request->all(), [
            'name' => 'required|max:64',
            'email' => 'required|email|max:64|unique:App\Models\User,email',
            // 'token' => 'required'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // check if the main users table has email already
        // $base_url= 'https://app-office36ty.protracked.in';

        $result = [];
        $count = CentralUser::where('email', $request->email)->get();
        //   return sizeof( $count);
        if (sizeof($count) > 0) {
            // return "no";
            $centralUser = tenancy()->central(function ($tenant) use ($request) {
                //   $centralUser = CentralUser::where('email', $request->email)->get();
                // return $centralUser
                $centralUser = CentralUser::firstOrCreate(
                    [
                        'email' => $request->email
                    ],
                    [
                        'name' => $request->name,
                        'status' => CentralUser::STATUS_PENDING,
                    ]
                );
                $centralUser->tenants()->attach($tenant);
                return $centralUser;
            });
            // return $centralUser;
            $user = User::where('email', $centralUser->email)->first();
            if ($request->name != $user->name)  $user->display_name = $request->name;
            $user->avatar =  'https://ui-avatars.com/api/?name=' . $request->name;
            $user->status = User::STATUS_PENDING;
            $user->update();
            // return $user;
            tenancy()->central(function ($tenant) use ($centralUser) {
                $organization = $tenant->organization()->first();
                $token = [
                    'tenant_id' => $organization->tenant_id,
                    'email' => $centralUser->email,
                ];
                $url = env('BASE_URL').'/accept-invitation?token=' . Crypt::encryptString(json_encode($token));


                Mail::to($centralUser->email)->send(new JoiningInvitationMail($centralUser, $organization, $url));
            });
            $result = User::select('id', 'name', 'email', 'status')->find($user->id);
        }
        // return "hb";

        if (sizeof($count) === 0) {
            // return "h";

            $centralUser = tenancy()->central(function ($tenant) use ($request) {
                $centralUser = CentralUser::firstOrCreate(
                    [
                        'email' => $request->email
                    ],
                    [

                        'name' => $request->name,

                        'status' => CentralUser::STATUS_PENDING,
                    ]
                );
                $centralUser->tenants()->attach($tenant);
                return $centralUser;
            });

            $user = User::where('email', $centralUser->email)->first();
            if ($request->name != $user->name)  $user->display_name = $request->name;
            $user->avatar =  'https://ui-avatars.com/api/?name=' . $request->name;
            $user->status = User::STATUS_PENDING;
            $user->update();

            // Joining Invitation Mail from Organization. -> Join / Decline
            tenancy()->central(function ($tenant) use ($centralUser) {
                $organization = $tenant->organization()->first();
                $token = [
                    'tenant_id' => $organization->tenant_id,
                    'email' => $centralUser->email,
                ];

                $url = env('BASE_URL').'/invitation?token=' . Crypt::encryptString(json_encode($token));

                Mail::to($centralUser->email)->send(new JoiningInvitationMail($centralUser, $organization, $url));
            });


            $result = User::select('id', 'name', 'email', 'status')->find($user->id);
        }

        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        $this->response["data"] = $result;
        return response()->json($this->response);
    }


    public function AcceptInvite(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'token' => 'required',
            // 'token' => 'required'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        try {
            $tokenData = json_decode(Crypt::decryptString($request->token));
        } catch (DecryptException $e) {
            $this->response["message"] = __('strings.something_wrong');
            return response()->json($this->response, 401);
        }
        // return $tokenData;
        $centralUser = CentralUser::where('email', $tokenData->email)->first();
        if ($centralUser) {

            if ($centralUser->status == CentralUser::STATUS_PENDING && !$request->password) {
                $this->response["message"] = __('strings.something_wrong');
                return response()->json($this->response, 401);
            }

            $tenant = $centralUser->tenants()->find($tokenData->tenant_id);
            if ($tenant) {

                $user = $tenant->run(function ($tenant) use ($centralUser) {
                    return User::where("email", $centralUser->email)->first();
                });
                if ($user && $user->status == User::STATUS_PENDING) {

                    if ($request->password) $centralUser->password = Hash::make($request->password);
                    $centralUser->status = CentralUser::STATUS_ACTIVE;
                    $centralUser->update();

                    $tenant->run(function ($tenant) use ($centralUser) {
                        $user = User::where("email", $centralUser->email)->first();
                        $user->status = User::STATUS_ACTIVE;
                        $user->update();
                    });

                    if (!$centralUser->hasVerifiedEmail()) $centralUser->markEmailAsVerified();


                    $this->response["status"] = true;
                    $this->response["message"] = __('strings.invitation_accept_success');
                    return response()->json($this->response);
                }
                $this->response["message"] = __('strings.invitation_accept_failed');
                return response()->json($this->response);
            }
        }
    }
    public function declineInvite(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        try {
            $tokenData = json_decode(Crypt::decryptString($request->token));
        } catch (DecryptException $e) {
            $this->response["message"] = __('strings.something_wrong');
            return response()->json($this->response, 401);
        }

        $centralUser = CentralUser::where('email', $tokenData->email)->first();
        if ($centralUser) {

            $tenant = $centralUser->tenants()->find($tokenData->tenant_id);
            if ($tenant) {

                $user = $tenant->run(function ($tenant) use ($centralUser) {
                    return User::where("email", $centralUser->email)->first();
                });
                if ($user && $user->status == User::STATUS_PENDING) {

                    $tenant->run(function ($tenant) use ($centralUser) {
                        $user = User::where("email", $centralUser->email)->first();
                        $user->status = User::STATUS_DECLINED;
                        $user->update();
                    });
                    // return $tokenData;
                    $tenant_users = $centralUser->tenants()->where('tenant_id', $tokenData->tenant_id)->first();



                    if ($tenant_users->pivot->forceDelete()) {

                        $this->response["status"] = true;
                        $this->response["message"] = __('strings.invitation_decline_success');
                        return response()->json($this->response);
                    }
                }
                $this->response["message"] = __('strings.invitation_decline_failed');
                return response()->json($this->response);
            }
        }

        // return 'j';
        // $validator = Validator::make($request->all(), [
        //     'token' => 'required',
        //      // 'token' => 'required'
        //  ]);
        //  if ($validator->fails()) {
        //      $this->response["code"] = "INVALID";
        //      $this->response["message"] = $validator->errors()->first();
        //      $this->response["errors"] = $validator->errors();
        //      return response()->json($this->response, 422);
        //  }

        //  $user_details = Crypt::decryptString($request->token);
        // $obj = json_decode($user_details);


        //  $centralUser->tenants()->attach($obj->tenant_id);

        // $dbname = $request->header('X-Tenant');
        // if($dbname){
        //     // return $dbname;
        //     // $dbname = config('tenancy.database.prefix').strtolower($dbname);
        //     $this->switchingDB('oas36ty_org_'.$dbname);
        // }
        // if(!$dbname){

        //     $this->switchingDB('oas36ty_org_'.$obj->tenant_id);
        // }

        //     $user =  User::where('email', $obj->email)->update([
        //         'status' => User::STATUS_DECLINED
        //     ]);

        //     // $centralUser = User::where('email', $obj->email)->first();
        //     // return $centralUser;
        //     // return DB::connection('mysql')->table('tenant_users')->get();
        //     // $centralUser->tenants()->with('organization')->latest('global_user_id')->first()->forceDelete();
        //    if($user){
        //        $this->response["status"] = true;
        //        $this->response["message"] = __('strings.invitation_decline_success');
        //        return response()->json($this->response);
        //     }
        //     $this->response["message"] = __('strings.invitation_decline_failed');
        //     return response()->json($this->response);

    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"users"},
     *     path="/users/{userID}",
     *     operationId="putUsers",
     *     summary="Update User",
     *     description="Update User",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="userID", in="path", required=true, description="User ID"),
     *     @OA\RequestBody(
     *          required=true,
     *  @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     description="file to upload",
     *                     property="file",
     *                     type="file",
     *                ),
     *                 required={"file"}
     *             )
     *         ),
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="naveen.w3master@gmail.com", description=""),
     *             @OA\Property(property="name", type="string", example="John Doe", description=""),
     *             @OA\Property(property="image", type="file", example="", description="", format="binary"),
     * 
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Updated successfully"),
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
     *                  property="user_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected user_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    private function getFileName($image, $namePrefix)
{
    list($type, $file) = explode(';', $image);
    list(, $extension) = explode('/', $type);
    list(, $file) = explode(',', $file);
    $result['name'] = $namePrefix . '.' . $extension;
    $result['file'] = $file;
    return $result;
}
    public function update(Request $request, $id)
    {
        $user = $request->user();


        $validator = Validator::make(['user_id' => $id] + $request->all(), [
            'user_id' => 'required|exists:App\Models\User,id',
            // 'image' => 'required',
            'name' => 'required',
            'emails' => 'nullable',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tenantName = $request->header('X-Tenant');
        $tenantName = config('tenancy.database.prefix').strtolower($tenantName);

        // return $request->image;
        
        // $path = tenant_asset($stored);
        $member = User::find($id);
        if ($member->status == User::STATUS_PENDING) {
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, Response::HTTP_FORBIDDEN);
        }
        // return $member;

        $oldCentralUser = tenancy()->central(function ($tenant) use ($member) {
            return CentralUser::where(['email' => $member->email])->first();
        });
      
        $oldCentralUserTenantsCount = tenancy()->central(function ($tenant) use ($oldCentralUser) {
            return $oldCentralUser->tenants()->count();
        });
        if($request->input('image')){

            $base64String = $request->input('image');
            // $base64String= "base64 string";
            
            // $image = $request->image; // the base64 image you want to upload
            $slug = time().$user->id; //name prefix
            $avatar = $this->getFileName($base64String, $slug);
            Storage::disk('s3')->put('user-images/' . $avatar['name'],  base64_decode($avatar['file']), 'public');
            $url = Storage::disk('s3')->url('user-images/' . $avatar['name']);
        }

// $p = Storage::disk('s3')->put('' . $imageName, $image, 'public'); 

// $image_url = Storage::disk()->url($imageName);
        
        // $imgdata = base64_decode($base64File);
        // $mime = getImageP

        // $image_parts = explode(";base64,", $base64File);
        // $image_type_aux = explode("image/", $image_parts[0]);
        // $image_type = $image_type_aux[1];
        // $image_base64 = base64_decode($image_parts[1]);
        
        // $this->uploadFile();
        // $path = "user-images/";
        //  $request->request->add(['image' => $base64File]);
        // return $request->all();
        // $check = $this->uploadFile('', 'image', $path);
        // return $check;
        // decode the base64 file
        // $fileData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $base64File));
        // // return $fileData;
        // // save it to temporary dir first.
        // return file_get_contents($fileData);
        // $tmpFilePath = sys_get_temp_dir() . '/' . Str::uuid()->toString();
        // // file_put_contents($tmpFilePath, $fileData);
        // // this just to help us get file info.
        // $tmpFile = new FileFile($tmpFilePath);
        
        // $file = new UploadedFile(
        //     // $tmpFile->getPathname(),
        //     $tmpFile->getFilename(),
        //     $tmpFile->getMimeType(),
        //     0,
        //     true // Mark it as test, since the file isn't from real HTTP POST.
        // );
        // return $file;
        // if ($oldCentralUserTenantsCount == 1) {
            $member->name = $request->name;
            if($request->input('image')){

                $member->avatar = $url;
            // }
            // $member->avatar = $imageName;
            $member->update();

        }
        // $tenant = $oldCentralUser->tenants()->find($tenantName);
        // return $id;
        $this->switchingDB($tenantName);
            // $user = $tenant->run(function ($tenant) use ($oldCentralUser, $request) {
                if($request->emails){

                
                foreach($request->emails as $all_email){
                   $exists_user_emails =  UserEmail::where(['user_id' => $id, 'emails_setting_id' => $all_email['id']])->get();

                    if(count($exists_user_emails) >= 1){
                        $this->response['status'] = true;
                        $this->response["message"] = 'Emails are already assigned choose another email';
                        return response()->json($this->response,200);
                    }else{
                        // $email_of_user = User::where('id', $id)->first()
                        UserEmail::create([
                           'user_id' => $id,
                           'emails_setting_id' => $all_email['id']
                           
                       ]);
                        
                    }
                    
                }
            }
            // });
 //else {
      

            
            // if ($oldCentralUserTenantsCount == 1) {
            //     tenancy()->central(function ($tenant) use ($oldCentralUser) {
            //         $oldCentralUser->delete();
            //     });
            // }
        //}

        $this->response["status"] = true;
        $this->response["message"] = [
            'msg' => __('strings.update_success'),
    
        ];
   
        return response()->json($this->response);
    }


    /**
     *
     * @OA\Delete(
     *     security={{"bearerAuth":{}}},
     *     tags={"users"},
     *     path="/users/{userID}",
     *     operationId="deleteUsers",
     *     summary="Delete User",
     *     description="Delete User",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="userID", in="path", required=true, description="User ID"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Record deleted successfully"),
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
     *                  property="user_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected user_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        $validator = Validator::make(['user_id' => $id], [
            'user_id' => 'required|exists:App\Models\User,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $member = User::find($id);
        // return  $member->global_id;
       $delete_tenant_user =  DB::connection('mysql')->table('tenant_users')->where('global_user_id', $member->global_id);
        $delete_user = DB::connection('mysql')->table('users')->where('global_id', $member->global_id);
       if($delete_tenant_user->delete() && $delete_user->delete()){

           // if ($member->status != User::STATUS_PENDING && $member->status != User::STATUS_DECLINED ) {
               //     $this->response["message"] = __('strings.destroy_failed');
               //     return response()->json($this->response, Response::HTTP_FORBIDDEN);
               // }
               if ($member->forceDelete()) {
                   $this->response["status"] = true;
                   $this->response["message"] = __('strings.destroy_success');
                   return response()->json($this->response);
                }
                
                $this->response["message"] = __('strings.destroy_failed');
                return response()->json($this->response);
            }
            $this->response["message"] = 'Tenant user deletion failed';
                return response()->json($this->response);
    }
            

    /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"users"},
     *     path="/users/{userID}/deactivate",
     *     operationId="deactivateUser",
     *     summary="Deactivate User",
     *     description="Deactivate User",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="userID", in="path", required=true, description="User ID"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Deactivated successfully"),
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
     *                  property="user_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected user_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function deactivate(Request $request, $id)
    {
        $user = $request->user();

        $validator = Validator::make(['user_id' => $id], [
            'user_id' => 'required|exists:App\Models\User,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $member = User::find($id);
        if ($member->status != User::STATUS_ACTIVE) {
            $this->response["message"] = __('strings.store_failed');
            return response()->json($this->response, Response::HTTP_FORBIDDEN);
        }

        $member->status = User::STATUS_INACTIVE;
        if ($member->update()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.store_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.store_failed');
        return response()->json($this->response);
    }
}
