<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\RecordPayment;
use App\Models\RecordPaymentInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RecordPaymentController extends Controller
{
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Record Payment"},
     *     path="/record-payment",
     *     operationId="getRecordPayment",
     *     summary="Fetch all record payment",
     *     description="Record Payment",
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
     *                         property="client_id",
     *                         type="integer",
     *                         example = 1
     *                      ),
     *                      @OA\Property(
     *                         property="payment_mode",
     *                         type="string",
     *                         example="Bank Transfer"
     *                      ),
     *                      @OA\Property(
     *                         property="branch_id",
     *                         type="integer",
     *                         example=1
     *                      ),
     *                      @OA\Property(
     *                         property="amount",
     *                         type="double",
     *                         example= 2500
     *                      ),
     *                      @OA\Property(
     *                         property="pay_date",
     *                         type="date",
     *                         example="16 Jan 2023"
     *                      ),
     *                       @OA\Property(
     *                         property="reference_id",
     *                         type="string",
     *                         example="APQWERT74673KJD"
     *                      ),
     *                    @OA\Property(
     *                         property="notes",
     *                         type="string",
     *                         example="Thank you for your business"
     *                      ),
     *                   
     *              @OA\Property(
     *                  property="invoice",
     *                  type="array",
     *                  @OA\Items(
     *                        @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="proposal_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  
     *                   @OA\Property(
     *                         property="invoice_number",
     *                         type="string",
     *                         example="yr 22-23/001"
     *                      ),
     *                   @OA\Property(
     *                         property="amount",
     *                         type="double",
     *                         example="4000.00"
     *                      ),
     *                  ),
     *              ),
     *                  @OA\Property(
     *                  property="client",
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
     *                         example="Tata consultancy services"
     *                      ),
     *                  ),
     *              ),
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
        $invoices = RecordPayment::select('client_id')->with(['recordPayInvoice',
        'invoice'=> function($q){
            $q->select('invoices.id','invoice_number','total_amt','amount');
        } 
        ,'audits'])->orderBy('id', 'DESC')->get();

        // $invoices = RecordPayment::select('client_id')->with([
        // 'recordPayInvoice',
        // 'invoice'=> function($q){
        //     $q->select('invoices.id','invoice_number','total_amt','amount');
        // } 
        // ,'audits'])->orderBy('id', 'DESC')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $invoices ;
        return response()->json($this->response);
    }

/**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Record Payment"},
     *     path="/record-payment",
     *     operationId="postRecordPayment",
     *     summary="Create new record payment",
     *     description="Record Payment",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="task_id", type="integer", example="1", description=""),
     *             @OA\Property(property="client_id", type="integer", example=1, description=""),
     *             @OA\Property(property="branch_id", type="integer", example=1, description=""),
     *             @OA\Property(property="pay_date", type="date", example="2023-01-06", description=""),
     *             @OA\Property(property="payment_mode", type="string", example="Bank Transfer", description=""),
     *             @OA\Property(property="amount", type="double", example=2000, description=""),
     *             @OA\Property(property="reference_id", type="string", example="QKSLDF4343LKJS", description=""),
     *             @OA\Property(property="notes", type="string", example="Thank you for Business...", description=""),              
     * 
     *              @OA\Property(
     *              property="invoice", 
     *              type="array",
     *              @OA\Items(
     *               @OA\property(
     *                  property = "invoice_id",
     *                  type = "integer",   
     *                  example = 1 
     *                   ),
     *                  @OA\property(
     *                         property = "tds_deducted",
     *                         type="double",
     *                         example= 2500
     *                  ),
     *               @OA\property(
     *                         property = "paid_amount",
     *                         type="double",
     *                         example="10000.00"
     *                  ),
     *          ),
     *        ),
     * 
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
            'task_id' => 'required|exists:App\Models\Task,id',
            'client_id'=>'required|exists:App\Models\Company,id',
            'payment_mode'=>'required',
            'branch_id'=>'required|exists:App\Models\Branch,id',
            'amount'=>'required',
            'pay_date'=> 'required',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        
        $recordPay = new RecordPayment($request->all());
     
        $recordPay->save();

        if($request->invoice ){
            foreach($request->invoice as $row){
                $data_arr = [
                    'record_payment_id' => $recordPay->id,
                    'invoice_id' => $row['invoice_id'],
                    'tds_deducted' => $row['tds_deducted'],
                    'paid_amount' => $row['paid_amount']
                ];
                RecordPaymentInvoice::create($data_arr);
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
     *     tags={"Record Payment"},
     *     path="/record-payment/{recordPay_id}",
     *     operationId="showtRecordPayment",
     *     summary="Show record payment",
     *     description="Record Payment",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="recordPay_id", in="path", required=true, description="Record Payment ID"),
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
     *                         property="client_id",
     *                         type="integer",
     *                         example = 1
     *                      ),
     *                      @OA\Property(
     *                         property="payment_mode",
     *                         type="string",
     *                         example="Bank Transfer"
     *                      ),
     *                      @OA\Property(
     *                         property="branch_id",
     *                         type="integer",
     *                         example=1
     *                      ),
     *                      @OA\Property(
     *                         property="amount",
     *                         type="double",
     *                         example= 2500
     *                      ),
     *                      @OA\Property(
     *                         property="pay_date",
     *                         type="date",
     *                         example="16 Jan 2023"
     *                      ),
     *                       @OA\Property(
     *                         property="reference_id",
     *                         type="string",
     *                         example="APQWERT74673KJD"
     *                      ),
     *                    @OA\Property(
     *                         property="notes",
     *                         type="string",
     *                         example="Thank you for your business"
     *                      ),
     *                   
     *              @OA\Property(
     *                  property="invoice",
     *                  type="array",
     *                  @OA\Items(
     *                        @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="proposal_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  
     *                   @OA\Property(
     *                         property="invoice_number",
     *                         type="string",
     *                         example="yr 22-23/001"
     *                      ),
     *                   @OA\Property(
     *                         property="total_amt",
     *                         type="double",
     *                         example="4000.00"
     *                      ),
     *                  ),
     *              ),
     *                  @OA\Property(
     *                  property="client",
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
     *                         example="Tata consultancy services"
     *                      ),
     *                  ),
     *              ),
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
        $validator = Validator::make(['task_id' => $id], [
            'task_id' => 'required|exists:App\Models\Task,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        
        $invoice = RecordPayment::where('task_id',$id)->with(['recordPayInvoice',
        'invoice'=> function($q){
            $q->select('invoices.id','invoice_number','total_amt','amount');
        } 
        ,'audits'])->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $invoice;
        return response()->json($this->response);
    }


    public function update(Request $request, $id)
    {
        $validator = Validator::make(['recordPay_id' => $id] + $request->all(), [
            'recordPay_id' => 'required|exists:App\Models\RecordPayment,id',
            'task_id' => 'required|exists:App\Models\Task,id',
            'client_id'=>'required|exists:App\Models\Company,id',
            'payment_mode'=>'required',
            'branch_id'=>'required|exists:App\Models\Branch,id',
            'amount'=>'required',
            'pay_date'=> 'required',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $recordPaid = RecordPayment::find($id);
        if(!$recordPaid){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $recordPaid->fill($request->only(['task_id','client_id','payment_mode','branch_id','amount','pay_date','reference_id','notes']));
        $recordPaid->update();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
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
