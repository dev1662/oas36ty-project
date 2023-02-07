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
use App\Models\Category;
use App\Models\CategoryUser;
use App\Models\EmailSignature;
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
        $user = $request->user();
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
        $users_emails = UserEmail::with([
            'users' => function ($q) use ($search) {
                $q->select('id', 'name', 'avatar', 'email', 'status','phone','location','designation_id','user_role_id')
                    // ->where(function ($q) use ($search) {
                    //     if ($search) $q->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%');
                    // })
                    // ->latest()
                ;
            },
            'EmailsSetting' => function ($q) use ($user) {
                $q->select('id', 'email', 'inbound_status', 'outbound_status');
            }
        ])->get();

        $users =  User::with(['branches'])->select('id', 'name', 'avatar','branch_id', 'email', 'status','phone','location','designation_id','user_role_id')->where(function ($q) use ($search) {
            if ($search) $q->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%');
        })->latest()->get();


        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] =
            [
                "users" => $users,
                "user_emails" => $users_emails
                //  "path" => $path
            ];
       
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
     *             @OA\Property(property="branch_id", type="integer", example="1", description=""),
     *             @OA\Property(property="user_role_id", type="integer", example="1", description=""),
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:64',
            // 'branch_id' => 'required',
            'email' => 'required|email|max:64|unique:App\Models\User,email',
            // 'token' => 'required',
            'user_role_id'=>'required|exists:App\Models\UserRole,id'
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
                        'status' => CentralUser::STATUS_ACTIVE,
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
            $user->user_role_id = $request->user_role_id;
            $user->update();

            if ($request->emails) {


                UserEmail::where(['user_id' => $user->id])->forceDelete();
    
    
                foreach ($request->emails as $all_email) {
                    $exists_user_emails =  UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $all_email['id']])->first();
    
                    if (!$exists_user_emails) {
                        UserEmail::create([
                            'user_id' => $user->id,
                            'emails_setting_id' => $all_email['id']
    
                        ]);
                    }
                    // if(count($exists_user_emails) >= 1){
                    //     $this->response['status'] = true;
                    //     $this->response["message"] = 'Emails are already assigned choose another email';
                    //     return response()->json($this->response,200);
                    // }else{
                    //     // $email_of_user = User::where('id', $id)->first()
                    //     UserEmail::create([
                    //        'user_id' => $id,
                    //        'emails_setting_id' => $all_email['id']
    
                    //    ]);
    
                    // }
    
                }
            }

            if($request->category){
                foreach ($request->category as $key => $categories) {
                    // $check = CategoryUser::where()
                  $cat_user = CategoryUser::create([
                    'user_id' => $user['id'],
                    'category_id' => $categories['id']
                  ]);
                }
            }
            // return $user;
            tenancy()->central(function ($tenant) use ($centralUser) {
                $organization = $tenant->organization()->first();
                $token = [
                    'tenant_id' => $organization->tenant_id,
                    'email' => $centralUser->email,
                ];
                $url = env('BASE_URL') . '/accept-invitation?token=' . Crypt::encryptString(json_encode($token));


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
            $branch_id = $request->branch_id ?? 1; 
            $user->user_role_id = $request->user_role_id;
            if($branch_id){
            $user->branch_id = $request->branch_id ?? 1;
            }
            $user->update();
            if ($request->emails) {


                UserEmail::where(['user_id' => $user->id])->forceDelete();
    
    
                foreach ($request->emails as $all_email) {
                    $exists_user_emails =  UserEmail::where(['user_id' => $user->id, 'emails_setting_id' => $all_email['id']])->first();
    
                    if (!$exists_user_emails) {
                        UserEmail::create([
                            'user_id' => $user->id,
                            'emails_setting_id' => $all_email['id']
    
                        ]);
                    }
                    // if(count($exists_user_emails) >= 1){
                    //     $this->response['status'] = true;
                    //     $this->response["message"] = 'Emails are already assigned choose another email';
                    //     return response()->json($this->response,200);
                    // }else{
                    //     // $email_of_user = User::where('id', $id)->first()
                    //     UserEmail::create([
                    //        'user_id' => $id,
                    //        'emails_setting_id' => $all_email['id']
    
                    //    ]);
    
                    // }
    
                }
            }

            if($request->category){
                foreach ($request->category as $key => $categories) {
                    // $check = CategoryUser::where()
                  $cat_user = CategoryUser::create([
                    'user_id' => $user['id'],
                    'category_id' => $categories['id']
                  ]);
                }
            }

            // Joining Invitation Mail from Organization. -> Join / Decline
            tenancy()->central(function ($tenant) use ($centralUser) {
                $organization = $tenant->organization()->first();
                $token = [
                    'tenant_id' => $organization->tenant_id,
                    'email' => $centralUser->email,
                ];

                $url = env('BASE_URL') . '/invitation?token=' . Crypt::encryptString(json_encode($token));

                Mail::to($centralUser->email)->send(new JoiningInvitationMail($centralUser, $organization, $url));
            });


            $result = User::select('id', 'name', 'email','user_role_id', 'status')->find($user->id);
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
        $user = User::find($id);
        $this->response['status'] = true;
        $this->response['message'] = 'user Fetched';
        $this->response['data'] = $user;
        return response()->json($this->response);
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
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="naveen.w3master@gmail.com", description=""),
     *             @OA\Property(property="name", type="string", example="John Doe", description=""),
     *             @OA\Property(property="branch_id", type="integer", example="1", description=""),
     *             @OA\Property(property="user_role_id", type="integer", example="1", description=""),
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
            'user_role_id'=>'required|exists:App\Models\UserRole,id'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tenantName = $request->header('X-Tenant');
        $tenantName = config('tenancy.database.prefix') . strtolower($tenantName);

        $member = User::find($id);

        $oldCentralUser = tenancy()->central(function ($tenant) use ($member) {
            return CentralUser::where(['email' => $member->email])->first();
        });

        $oldCentralUserTenantsCount = tenancy()->central(function ($tenant) use ($oldCentralUser) {
            return $oldCentralUser->tenants()->count();
        });
        if ($request->input('image')) {

            $base64String = $request->input('image');
  
            $slug = time() . $user->id; //name prefix
            $avatar = $this->getFileName($base64String, $slug);
            Storage::disk('s3')->put('user-images/' . $avatar['name'],  base64_decode($avatar['file']), 'public');
            $url = Storage::disk('s3')->url('user-images/' . $avatar['name']);
        }

        $member->name = $request->name;
        $member->branch_id = $request->branch_id ?? 1;
        $member->user_role_id = $request->user_role_id;


        if ($request->input('image')) {

            $member->avatar = $url;

        }
        $member->update();
        $this->switchingDB($tenantName);
        if ($request->emails) {


            UserEmail::where(['user_id' => $id])->forceDelete();


            foreach ($request->emails as $all_email) {
                $exists_user_emails =  UserEmail::where(['user_id' => $id, 'emails_setting_id' => $all_email['id']])->first();

                if (!$exists_user_emails) {
                    UserEmail::create([
                        'user_id' => $id,
                        'emails_setting_id' => $all_email['id']

                    ]);
                }
            }
        }

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
        if ($delete_tenant_user->delete() && $delete_user->delete()) {

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


    public function reInvite(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            // 'email' => 'required|email|max:64',
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
        
        $check_user = User::find($request->id);
        $result = [];
        $count = CentralUser::where(['email'=> $check_user->email, 'status'=>CentralUser::STATUS_ACTIVE])->get();
        $centralUser = CentralUser::where('email', $check_user->email)->first();
        // return $centralUser1;
        //   return sizeof( $count);
    
        if (sizeof($count) > 0) {
            // return "no";
            // $centralUser = tenancy()->central(function ($tenant) use ($check_user) {
            //     //   $centralUser = CentralUser::where('email', $request->email)->get();
            //     // return $centralUser
            //     $centralUser = CentralUser::firstOrCreate(
            //         [
            //             'email' => $check_user->email
            //         ],
            //         [
            //             'name' => $check_user->name,
            //             'status' => CentralUser::STATUS_PENDING,
            //         ]
            //     );
            //     $centralUser->tenants()->attach($tenant);
            //     return $centralUser;
            // });
            // CentralUser::where('email', $check_user->email)->update(['status' => CentralUser::STATUS_PENDING]);
            // return $centralUser;
            $user = User::where('email', $centralUser->email)->first();
            // if ($request->name != $user->name)  $user->display_name = $request->name;
            $user->avatar =  'https://ui-avatars.com/api/?name=' . $request->name;
            $user->status = User::STATUS_PENDING;
            $user->update();

           
            tenancy()->central(function ($tenant) use ($centralUser) {
                $organization = $tenant->organization()->first();
                $token = [
                    'tenant_id' => $organization->tenant_id,
                    'email' => $centralUser->email,
                ];
                $url = env('BASE_URL') . '/accept-invitation?token=' . Crypt::encryptString(json_encode($token));


                Mail::to($centralUser->email)->send(new JoiningInvitationMail($centralUser, $organization, $url));
            });
            $result = User::select('id', 'name', 'email', 'status')->find($user->id);
        }
        else if ($check_user && $centralUser) {
            // return "h";
            CentralUser::where('email', $check_user->email)->update(['status' => CentralUser::STATUS_PENDING]);
           $user = $check_user;

            // $user = User::where('email', $centralUser->email)->first();
            if ($user->name)  $user->display_name = $user->name;
            $user->avatar =  'https://ui-avatars.com/api/?name=' . $user->name;
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
        $this->response["message"] = __('strings.reinvite_success');
        $this->response["data"] = $result;
        return response()->json($this->response);
    }

   
        /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"users"},
     *     path="/users/{userID}/update-profile",
     *     operationId="putUsersUpdateProfile",
     *     summary="Update User profile",
     *     description="Update User profile",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="userID", in="path", required=true, description="User ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="phone", type="integer", example= 7488796725 , description=""),
     *             @OA\Property(property="name", type="string", example="John Doe", description=""),
     *             @OA\Property(property="location", type="string", example="Delhi India - 110014", description=""),
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

public function updateProfile(Request $request, $id){
        $validator = Validator::make(['user_id' => $id] + $request->all(), [
            'user_id' => 'required|exists:App\Models\User,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $user = User::find($id);
        if(!$user){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $user->fill($request->only(['phone','location','name']));
        $user->update();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }


     /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"users"},
     *     path="/users/{userID}/update-signature",
     *     operationId="putUpdateSignature",
     *     summary="Update User signature",
     *     description="Update User signature",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="userID", in="path", required=true, description="User ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="John Doe", description=""),
     *             @OA\Property(property="signature", type="string", example="Thanks & Regards - John Doe +91 7488796756 ..", description=""),
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
public function updateSignature(Request $request, $id){
    $validator = Validator::make(['user_id' => $id] + $request->all(), [
        'user_id' => 'required|exists:App\Models\User,id',
        'signature'=>'required',
        'name'=>'required'
    ]);
    if ($validator->fails()) {
        $this->response["code"] = "INVALID";
        $this->response["message"] = $validator->errors()->first();
        $this->response["errors"] = $validator->errors();
        return response()->json($this->response, 422);
    }

    $user = User::find($id);
    $signature = EmailSignature::where('user_id',$id)->first();
    if($user){
        if($signature){
            $signature->fill($request->only('name','signature'));
            $signature->update();
        }else{
            $data_arr = [
                'user_id'=> $id,
                'name'=> $request->name,
                'signature'=>$request->signature,
            ];
            EmailSignature::create($data_arr);
        }

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
    }else{
    $this->response["message"] = __('strings.update_failed');
    return response()->json($this->response, 422);
    }
   
    return response()->json($this->response);
}


/**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"users"},
     *     path="/users/{userID}/get-signature",
     *     operationId="getSignature",
     *     summary="Fetch User signature",
     *     description="Fetch User signature",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="userID", in="path", required=true, description="User ID"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Fetched data successfully"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="user_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Jodn Doe"
     *                      ),
     *                      @OA\Property(
     *                         property="signature",
     *                         type="string",
     *                         example="Thanks & Regards - John Doe +91 7488796756 .."
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
public function getSignature($id){
    $validator = Validator::make(['user_id' => $id], [
        'user_id' => 'required|exists:App\Models\User,id',
    ]);
    if ($validator->fails()) {
        $this->response["code"] = "INVALID";
        $this->response["message"] = $validator->errors()->first();
        $this->response["errors"] = $validator->errors();
        return response()->json($this->response, 422);
    }

    $user = User::find($id);
    if($user){
        $signature = EmailSignature::where('user_id',$id)->first();
        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $signature;
    }else{
    $this->response["message"] = __('strings.fetch_failed');
    return response()->json($this->response, 422);
    }
   
    return response()->json($this->response);
}

}
