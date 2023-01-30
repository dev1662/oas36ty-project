<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\AllMaster;
use App\Models\Privileges;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AllMasterController extends Controller
{
   /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"All Masters"},
     *     path="/all-master",
     *     operationId="getAllMaster",
     *     summary=" Dsiplay all master",
     *     description="All masters",
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
     *                         property="name",
     *                         type="string",
     *                         example="Leads"
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
     *                  ),
     *              ),
     * 
     *               @OA\Property(
     *                  property="privileges",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="all_master_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="leads_edit"
     *                      ),
     *                    ),
     *                  ),
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
        // $this->switchingDB($dbname);
        $all_master = AllMaster::with([
            'privileges',            
            ])->orderBy('id', 'DESC')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $all_master;
        return response()->json($this->response);
    
    }

    /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"All Masters"},
     *     path="/all-master",
     *     operationId="postAllMaster",
     *     summary="Create new master",
     *     description="Create masters",
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Leads", description=""),
     *              
     *              @OA\Property(
     *              property="privileges", 
     *              type="array",
     *              @OA\Items(
     *                @OA\property(
     *                         property = "name",
     *                         type="string",
     *                         example="View all leads"
     *                  ),
     *                 
     *          ),
     *        ),
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
            'name' => 'required|unique:App\Models\AllMaster,name',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        $allmaster = new AllMaster($request->all());
        $allmaster->save();

        if($request->privileges){
            foreach($request->privileges as $row){
                $data_arr = [
                    'all_master_id' => $allmaster->id,
                    'name' => $row['name']
                ];
                Privileges::create($data_arr);
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
     *     tags={"All Masters"},
     *     path="/all-master/{master_id}",
     *     operationId="showAllMaster",
     *     summary=" Dsiplay master Details",
     *     description="Masters Details",
     *     @OA\Parameter(name="master_id", in="path", required=true, description="Master ID"),
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
     *                         property="name",
     *                         type="string",
     *                         example="Leads"
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
     *                  ),
     *              ),
     * 
     *               @OA\Property(
     *                  property="privileges",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="all_master_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="leads_edit"
     *                      ),
     *                    ),
     *                  ),
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
        $validator = Validator::make(['master_id' => $id], [
            'master_id' => 'required|exists:App\Models\AllMaster,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        
        $masterDetails = AllMaster::where('id',$id)->with(['privileges'])->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $masterDetails;
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"All Masters"},
     *     path="/all-master/{master_id}",
     *     operationId="putAllMaster",
     *     summary="Update master Details",
     *     description="Update masters",
     *     @OA\Parameter(name="master_id", in="path", required=true, description="Master ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Leads", description=""),
     *              
     *              @OA\Property(
     *              property="privileges", 
     *              type="array",
     *              @OA\Items(
     *                @OA\property(
     *                         property = "name",
     *                         type="string",
     *                         example="View all leads"
     *                  ),
     *                 
     *          ),
     *        ),
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make(['master_id' => $id] + $request->all(), [
            'master_id' => 'required|exists:App\Models\AllMaster,id',
            'name'=>'required'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $masterDetails = AllMaster::find($id);
        if(!$masterDetails){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $masterDetails->fill($request->only(['name']));
        $masterDetails->update();
        Privileges::where('all_master_id',$id)->forceDelete();
        if($request->privileges){
            foreach($request->privileges as $row){
                $data_arr = [
                    'all_master_id' => $id,
                    'name' => $row['name']
                ];
                Privileges::create($data_arr);
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
     *     tags={"All Masters"},
     *     path="/all-master/{master_id}",
     *     operationId="deleteAllMaster",
     *     summary="Delete master Details",
     *     description="Delete masters",
     *     @OA\Parameter(name="master_id", in="path", required=true, description="Master ID"),
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
        $validator = Validator::make(['master_id' => $id], [
            'master_id' => 'required|exists:App\Models\AllMaster,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $masterDetails = AllMaster::find($id);
        if(!$masterDetails){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($masterDetails->forceDelete()) {
            Privileges::where('all_master_id',$id)->forceDelete();
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
