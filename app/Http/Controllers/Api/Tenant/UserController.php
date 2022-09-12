<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
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
use App\Models\EmailMaster;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use PDO;

class UserController extends Controller
{
    public function get_emails_to_assign(Request $request){

        $dbname = $request->header('X-Tenant'); //json_decode($request->header('currrent'))->tenant->organization->name;
        $dbname = config('tenancy.database.prefix').strtolower($dbname);
        // return   $dbname;
        $this->switchingDB($dbname);
    
        $details_arr = EmailMaster::where(['inbound_status' =>'tick', 'outbound_status' => 'tick'])->with(['emailInbound','emailOutbound'])->get();
    
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

        $users = User::select('id', 'name','avatar', 'email', 'status')->where(function ($q) use ($search) {
            if ($search) $q->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%');
        })->latest()->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $users;
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
            $user->avatar =  'https://ui-avatars.com/api/?name='.$request->name;
            $user->status = User::STATUS_PENDING;
            $user->update();
            // return $user;
            tenancy()->central(function ($tenant) use ($centralUser) {
                $organization = $tenant->organization()->first();
                $token = [
                    'tenant_id' => $organization->tenant_id,
                    'email' => $centralUser->email,
                ];
                $url = 'https://app-office36ty.protracked.in/accept-invitation?token=' . Crypt::encryptString(json_encode($token));


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
            $user->avatar =  'https://ui-avatars.com/api/?name='.$request->name;
            $user->status = User::STATUS_PENDING;
            $user->update();

            // Joining Invitation Mail from Organization. -> Join / Decline
            tenancy()->central(function ($tenant) use ($centralUser) {
                $organization = $tenant->organization()->first();
                $token = [
                    'tenant_id' => $organization->tenant_id,
                    'email' => $centralUser->email,
                ];

                $url = 'https://app-office36ty.protracked.in/invitation?token=' . Crypt::encryptString(json_encode($token));

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
                    
                    
                    
                        if($tenant_users->pivot->forceDelete()){

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
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="email", type="string", example="naveen.w3master@gmail.com", description=""),
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
    public function update(Request $request, $id)
    {
        $user = $request->user();
        return $user;

        $validator = Validator::make(['user_id' => $id] + $request->all(), [
            'user_id' => 'required|exists:App\Models\User,id',
            'image' => 'required|image',
            'name' => 'required',
            'email' => 'required|email|max:64|',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $member = User::find($id);
        if ($member->status != User::STATUS_PENDING) {
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, Response::HTTP_FORBIDDEN);
        }

        $oldCentralUser = tenancy()->central(function ($tenant) use ($member) {
            return CentralUser::where(['email' => $member->email])->first();
        });
        $newCentralUser = tenancy()->central(function ($tenant) use ($request) {
            return CentralUser::where(['email' => $request->email])->first();
        });
        $oldCentralUserTenantsCount = tenancy()->central(function ($tenant) use ($oldCentralUser) {
            return $oldCentralUser->tenants()->count();
        });

        if ($oldCentralUserTenantsCount == 1 && !$newCentralUser) {
            $member->email = $request->email;
            $member->update();
        } else {
            if (!$newCentralUser) {
                $newCentralUser = tenancy()->central(function ($tenant) use ($request, $member) {
                    return CentralUser::create([
                        'name' => $member->name,
                        'email' => $request->email,
                        'status' => CentralUser::STATUS_PENDING,
                    ]);
                });
            }

            tenancy()->central(function ($tenant) use ($oldCentralUser, $newCentralUser) {
                $tenant->users()->detach($oldCentralUser->global_id);
            });

            $member->global_id = $newCentralUser->global_id;
            $member->email = $request->email;
            $member->update();

            tenancy()->central(function ($tenant) use ($oldCentralUser, $newCentralUser) {
                $tenant->users()->syncWithoutDetaching([$newCentralUser->global_id]);
            });

            if ($oldCentralUserTenantsCount == 1) {
                tenancy()->central(function ($tenant) use ($oldCentralUser) {
                    $oldCentralUser->delete();
                });
            }
        }

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
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
        if ($member->status != User::STATUS_PENDING && $member->status != User::STATUS_DECLINED) {
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, Response::HTTP_FORBIDDEN);
        }

        if ($member->forceDelete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
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
