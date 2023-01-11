<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\BankDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BankDetailsController extends Controller
{
      /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Bank Details"},
     *     path="/bank-details",
     *     operationId="getBankDetails",
     *     summary="Fetch Bank Details",
     *     description="Bank Details",
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
     *                         property="account_name",
     *                         type="string",
     *                         example="Centrik Business solution Pvt Ltd"
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
     *    )
     */
    public function index(Request $request)
    {
   $dbname = $request->header('X-Tenant');
        $dbname = strtolower($dbname);
        // return $dbname;
        $this->switchingDB($dbname);
        $bankDetails = BankDetails::with([
            'audits'
            ])->orderBy('id', 'DESC')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $bankDetails;
        return response()->json($this->response);
    }

 /**
     *
     *    @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Bank Details"},
     *     path="/bank-details",
     *     operationId="postBankDetails",
     *     summary="Create Bank Details",
     *     description="Bank Details",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *              @OA\Property(
     *                         property="account_name",
     *                         type="string",
     *                         example="Centrik Business solution Pvt Ltd"
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
            'account_name' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'branch_name' => 'required',
            'account_type' => 'required'
           
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // return $request->all();
        $category = new BankDetails($request->all());
        $category->save();
       
        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    }

   
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Bank Details"},
     *     path="/bank-details/{bankDetailsId}",
     *     operationId="showBankDetails",
     *     summary="Show Bank Details",
     *     description="Bank Details",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="bankDetailsId", in="path", required=true, description="BankDetails ID"),
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
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="account_name",
     *                         type="string",
     *                         example="Centrik Business solution Pvt Ltd"
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
        $validator = Validator::make(['bank_det_id' => $id], [
            'bank_det_id' => 'required|exists:App\Models\BankDetails,id',
            
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $bank_details = BankDetails::find($id);

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $bank_details;
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"Bank Details"},
     *     path="/bank-details/{bankDetailsId}",
     *     operationId="putBankDetails",
     *     summary="Update Bank Details",
     *     description="Bank Details",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="bankDetailsId", in="path", required=true, description="BankDetails ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *                      @OA\Property(
     *                         property="account_name",
     *                         type="string",
     *                         example="Centrik Business solution Pvt Ltd"
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
     *                  property="category_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected category_id is invalid."
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
        $validator = Validator::make(['bank_det_id' => $id] + $request->all(), [
            'bank_det_id' => 'required|exists:App\Models\BankDetails,id',
            'account_name' => 'required',
            'bank_name' => 'required',
            'account_number' => 'required',
            'ifsc_code' => 'required',
            'branch_name' => 'required',
            'account_type' => 'required'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $bank_details = BankDetails::find($id);
        if(!$bank_details){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $bank_details->fill($request->only(['account_name', 'bank_name','account_number', 'ifsc_code', 'branch_name', 'account_type', 'micr_code','swift_code']));
        $bank_details->update();
        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }

   /**
     *
     * @OA\Delete(
     *      security={{"bearerAuth":{}}},
     *     tags={"Bank Details"},
     *     path="/bank-details/{bankDetailsId}",
     *     operationId="deleteBankDetails",
     *     summary="Delete Bank Details",
     *     description="Bank Details",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="bankDetailsId", in="path", required=true, description="BankDetails ID"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Deleted successfully"),
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
     *                  property="category_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected category_id is invalid."
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
        $validator = Validator::make(['bank_det_id' => $id], [
            'bank_det_id' => 'required|exists:App\Models\BankDetails,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
       
        $bank_details = BankDetails::find($id);
        if(!$bank_details){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($bank_details->forceDelete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
