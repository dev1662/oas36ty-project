<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Resources\TenantResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Models\Branch;
use App\Models\CentralUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Session;
use PDO;

class BranchController extends Controller
{
    
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"branches"},
     *     path="/branches",
     *     operationId="getBranches",
     *     summary="Branches",
     *     description="Branches",
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
     *                      @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Branch Name"
     *                      ),
     *                      @OA\Property(
     *                         property="bussiness_name",
     *                         type="string",
     *                         example="Bussiness Name"
     *                      ),
     *                      @OA\Property(
     *                         property="pan_number",
     *                         type="string",
     *                         example="GNBPK8989D"
     *                      ),
     *                    @OA\Property(
     *                         property="address",
     *                         type="string",
     *                         example="Address"
     *                      ),
     *                    @OA\Property(
     *                         property="website",
     *                         type="string",
     *                         example="https://rera.oas36ty.com/login#/"
     *                      ),
     *                      @OA\Property(
     *                         property="logo",
     *                         type="string",
     *                         example="https://oas36ty-files.s3.ap-south-1.amazonaws.com/user-images/16702182421.png"
     *                      ),
     *                  @OA\Property(
     *                  property="bussiness_type",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="bussiness_type",
     *                         type="string",
     *                         example="Branch Office"
     *                      ),
     *                  ),
     *              ),
     * 
     *               @OA\Property(
     *                  property="state_code",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="state_name",
     *                         type="string",
     *                         example="Delhi"
     *                      ),
     *                  ),
     *              ),
     *               @OA\Property(
     *                  property="bank",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="account_name",
     *                         type="string",
     *                         example="Delhi"
     *                      ),
     *                      @OA\Property(
     *                         property="bank_name",
     *                         type="string",
     *                         example="State bank of india"
     *                      ),
     *                      @OA\Property(
     *                         property="account_number",
     *                         type="integer",
     *                         example="002930293230309"
     *                      ),
     *                      @OA\Property(
     *                         property="ifsc_code",
     *                         type="string",
     *                         example="SBIN0000138"
     *                      ),
     *                      @OA\Property(
     *                         property="swift_code",
     *                         type="string",
     *                         example="SBININBB104"
     *                      ),
     *                      @OA\Property(
     *                         property="micr_code",
     *                         type="integer",
     *                         example="110002087"
     *                      ),
     *                      @OA\Property(
     *                         property="branch_name",
     *                         type="string",
     *                         example="New Delhi Main Branch"
     *                      ),
     *                      @OA\Property(
     *                         property="account_type",
     *                         type="string",
     *                         example="current"
     *                      ),
     *                  ),
     *              ),
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
        $dbname = $request->header('X-Tenant');
        $dbname = config('tenancy.database.prefix').strtolower($dbname);
        // return   $dbname;
        $this->switchingDB($dbname);
        // return json_decode($request->header('currrent'))->tenant->organization->name;

        $branches = Branch::select('id', 'name','type')->with('audits')->orderBy('id', 'DESC')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $branches ;
        $this->response['count'] = count($branches);
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"branches"},
     *     path="/branches",
     *     operationId="postBranch",
     *     summary="Create Branch",
     *     description="Create Branch",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Branch Name", description=""),
     *             @OA\Property(property="bussiness_name", type="string", example="Bussiness name", description=""),
     *             @OA\Property(property="bussiness_type", type="integer", example="1", description=""),
     *             @OA\Property(property="pan_number", type="string", example="PAN", description=""),
     *             @OA\Property(property="state_code", type="integer", example="1", description=""),
     *             @OA\Property(property="bank_id", type="integer", example="1", description=""),
     *             @OA\Property(property="address", type="string", example="Address", description=""),
     *             @OA\Property(property="website", type="string", example="https://rera.oas36ty.com/login#/", description=""),
     *             @OA\Property(property="logo", type="string", example="https://oas36ty-files.s3.ap-south-1.amazonaws.com/user-images/16702182421.png", description=""),
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Registered successfully"),
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
            'name' => 'required|max:64|unique:App\Models\Branch,name',
            'bussiness_name'=>'required',
            'mobile'=>'required',
            'bank_id'=>'required|exists:App\Models\BankDetails,id',
            'logo'=>'required'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        
        $branch = new Branch($request->all());
     
        $branch->save();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    }


    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"branches"},
     *     path="/branches/{branchID}",
     *     operationId="showBranch",
     *     summary="Show Branch",
     *     description="Show Branch",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="branchID", in="path", required=true, description="Branch ID"),
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
     *                      @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Branch Name"
     *                      ),
     *                      @OA\Property(
     *                         property="bussiness_name",
     *                         type="string",
     *                         example="Bussiness Name"
     *                      ),
     *                      @OA\Property(
     *                         property="pan_number",
     *                         type="string",
     *                         example="GNBPK8989D"
     *                      ),
     *                    @OA\Property(
     *                         property="address",
     *                         type="string",
     *                         example="Address"
     *                      ),
     *                    @OA\Property(
     *                         property="website",
     *                         type="string",
     *                         example="https://rera.oas36ty.com/login#/"
     *                      ),
     *                      @OA\Property(
     *                         property="logo",
     *                         type="string",
     *                         example="https://oas36ty-files.s3.ap-south-1.amazonaws.com/user-images/16702182421.png"
     *                      ),
     *                  @OA\Property(
     *                  property="bussiness_type",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="bussiness_type",
     *                         type="string",
     *                         example="Branch Office"
     *                      ),
     *                  ),
     *              ),
     * 
     *               @OA\Property(
     *                  property="state_code",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="state_name",
     *                         type="string",
     *                         example="Delhi"
     *                      ),
     *                  ),
     *              ),
     *               @OA\Property(
     *                  property="bank",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="account_name",
     *                         type="string",
     *                         example="Delhi"
     *                      ),
     *                      @OA\Property(
     *                         property="bank_name",
     *                         type="string",
     *                         example="State bank of india"
     *                      ),
     *                      @OA\Property(
     *                         property="account_number",
     *                         type="integer",
     *                         example="002930293230309"
     *                      ),
     *                      @OA\Property(
     *                         property="ifsc_code",
     *                         type="string",
     *                         example="SBIN0000138"
     *                      ),
     *                      @OA\Property(
     *                         property="swift_code",
     *                         type="string",
     *                         example="SBININBB104"
     *                      ),
     *                      @OA\Property(
     *                         property="micr_code",
     *                         type="integer",
     *                         example="110002087"
     *                      ),
     *                      @OA\Property(
     *                         property="branch_name",
     *                         type="string",
     *                         example="New Delhi Main Branch"
     *                      ),
     *                      @OA\Property(
     *                         property="account_type",
     *                         type="string",
     *                         example="current"
     *                      ),
     *                  ),
     *              ),
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
        $validator = Validator::make(['branch_id' => $id], [
            'branch_id' => 'required|exists:App\Models\Branch,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $branch = Branch::select('id', 'name')->find($id);

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $branch;
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"branches"},
     *     path="/branches/{branchID}",
     *     operationId="putBranch",
     *     summary="Update Branch",
     *     description="Update Branch",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="branchID", in="path", required=true, description="Branch ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string", example="Branch name", description=""),
     *             @OA\Property(property="bussiness_name", type="string", example="Bussiness name", description=""),
     *             @OA\Property(property="bussiness_type", type="integer", example="1", description=""),
     *             @OA\Property(property="pan_number", type="string", example="PAN", description=""),
     *             @OA\Property(property="state_code", type="integer", example="1", description=""),
     *             @OA\Property(property="bank_id", type="integer", example="1", description=""),
     *             @OA\Property(property="address", type="string", example="Address", description=""),
     *             @OA\Property(property="website", type="string", example="https://rera.oas36ty.com/login#/", description=""),
     *             @OA\Property(property="logo", type="string", example="https://oas36ty-files.s3.ap-south-1.amazonaws.com/user-images/16702182421.png", description=""),
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make(['branch_id' => $id] + $request->all(), [
            'branch_id' => 'required|exists:App\Models\Branch,id',
            'bussiness_name'=>'required',
            'mobile'=>'required',
            'bank_id'=>'required|exists:App\Models\BankDetails,id',
            'logo'=>'required'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $branch = Branch::find($id);
        if(!$branch){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $branch->fill($request->only(['name','bussiness_name','bussiness_type','pan_number','state_code','bank_id','address','website','logo']));
        $branch->update();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Delete(
     *     security={{"bearerAuth":{}}},
     *     tags={"branches"},
     *     path="/branches/{branchID}",
     *     operationId="deleteBranch",
     *     summary="Delete Branch",
     *     description="Delete Branch",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="branchID", in="path", required=true, description="Branch ID"),
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
        $validator = Validator::make(['branch_id' => $id], [
            'branch_id' => 'required|exists:App\Models\Branch,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $branch = Branch::find($id);
        if(!$branch){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($branch->forceDelete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
