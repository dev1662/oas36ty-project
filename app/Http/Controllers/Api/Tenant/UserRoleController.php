<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\UserAccessMaster;
use App\Models\UserAccessPrivileges;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserRoleController extends Controller
{
   /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Users Role"},
     *     path="/users-role",
     *     operationId="getUserRole",
     *     summary=" Dsiplay all Users Designation",
     *     description="Users Role",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Fetched all data successfully"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="designation_name",
     *                         type="string",
     *                         example="Owner"
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
     *                  ),
     *              ),
     * 
     *               @OA\Property(
     *                  property="users",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="John Doe"
     *                      ),
     *                   @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         example="active"
     *                      ),
     *                    ),
     *                  ),
     *           @OA\Property(
     *                  property="masters",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="user_role_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="all_master_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         example="active"
     *                      ),
     *                    ),
     *                  ),
     *              @OA\Property(
     *                  property="privileges",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="user_role_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="all_master_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="privilege_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="user_access_master_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                    ),
     *                  ),
     *                 
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
        $dbname = strtolower($dbname);
        // return $dbname;
        $this->switchingDB($dbname);
        $designation = UserRole::with([
            'users'
            // =>function($q){
            //     $q->select(['id','name','status']);
            // }
            ,
            'masters',
            'privileges',
            'audits'
            ])->orderBy('id')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $designation;
        return response()->json($this->response);
  
    }

   /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Users Role"},
     *     path="/users-role",
     *     operationId="postUserRole",
     *     summary=" Create new Designation",
     *     description="Users Role",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="designation_name", type="string", example="Owner", description=""),
     *              
     *              @OA\Property(
     *              property="masterAccess", 
     *              type="array",
     *              @OA\Items(
     *                @OA\property(
     *                         property = "master_id",
     *                         type="integer",
     *                         example="1"
     *                  ),
     *              @OA\Property(
     *              property="privileges", 
     *              type="array",
     *              @OA\Items(
     *                @OA\property(
     *                         property = "privileges_id",
     *                         type="integer",
     *                         example="1"
     *                          ),
     *                 
     *                      ),
     *                  ),
     * 
     *               ),
     *             ),
     * 
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Created successfully"),
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
     *                  property="name",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected name is invalid."
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
            'designation_name' => 'required',
           ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // return $request->all();
        $designation_det = new UserRole($request->all());
         $designation_det->save();

        if($request->masterAccess){
            foreach($request->masterAccess as $rows){
                $access_data = [
                    'user_role_id'=> $designation_det->id,
                    'all_master_id'=>$rows['master_id'],
                    'status'=> UserAccessMaster::STATUS_ACTIVE,
                ];
                $master_data = new UserAccessMaster($access_data);
                $master_data->save();
                foreach($rows['privileges'] as $row){
                $data_arr = [
                    'user_access_master_id' => $master_data->id,
                    'privilege_id' => $row['privileges_id'],
                    'user_role_id'=> $designation_det->id,
                    'all_master_id'=>$rows['master_id'],
                ];
                UserAccessPrivileges::create($data_arr);
            }
         }

        }
        
        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    
    }

    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Users Role"},
     *     path="/users-role/{userRole_id}",
     *     operationId="showUserRole",
     *     summary=" Dsiplay all Users Designation",
     *     description="Users Role",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="userRole_id", in="path", required=true, description="UserRole ID"),
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
     *                  @OA\Property(
     *                         property="designation_name",
     *                         type="string",
     *                         example="Owner"
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
     *                  ),
     *              ),
     * 
     *               @OA\Property(
     *                  property="users",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="John Doe"
     *                      ),
     *                   @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         example="active"
     *                      ),
     *                    ),
     *                  ),
     *           @OA\Property(
     *                  property="masters",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="user_role_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="all_master_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="status",
     *                         type="string",
     *                         example="active"
     *                      ),
     *                    ),
     *                  ),
     *              @OA\Property(
     *                  property="privileges",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="user_role_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="all_master_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="privilege_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="user_access_master_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                    ),
     *                  ),
     *                 
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
        $validator = Validator::make(['userRole_id' => $id], [
            'userRole_id' => 'required|exists:App\Models\UserRole,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $userRoleDetails = UserRole::where('id',$id)->with([
            'users',
            'masters',
            'privileges',
            'audits'
            ])->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $userRoleDetails;
        return response()->json($this->response);

    }

    
    public function update(Request $request, $id)
    {
        $validator = Validator::make(['userRole_id' => $id] + $request->all(), [
            'userRole_id' => 'required|exists:App\Models\UserRole,id',
            'designation_name'=>'required',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        
         $designation_det = UserRole::find($id);
        if(!$designation_det){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        if($request->masterAccess){
            foreach($request->masterAccess as $rows){
                $access_data = [
                    'user_role_id'=> $designation_det->id,
                    'all_master_id'=>$rows['master_id'],
                    'status'=> UserAccessMaster::STATUS_ACTIVE,
                ];
                $master_data = new UserAccessMaster($access_data);
                $master_data->save();
                foreach($rows['privileges'] as $row){
                $data_arr = [
                    'user_access_master_id' => $master_data->id,
                    'privilege_id' => $row['privileges_id'],
                    'user_role_id'=> $designation_det->id,
                    'all_master_id'=>$rows['master_id'],
                ];
                UserAccessPrivileges::create($data_arr);
            }
         }

        }
        
        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
