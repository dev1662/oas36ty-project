<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
      
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Invoices"},
     *     path="/invoices",
     *     operationId="getinvoice",
     *     summary="Fetch all invoices",
     *     description="Invoices",
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
     *                         property="client_gst_number",
     *                         type="string",
     *                         example="09AKNJK4898M1V9"
     *                      ),
     *                      @OA\Property(
     *                         property="state_code",
     *                         type="integer",
     *                         example=07
     *                      ),
     *                      @OA\Property(
     *                         property="invoice_number",
     *                         type="string",
     *                         example="yr 22-23/0173"
     *                      ),
     *                      @OA\Property(
     *                         property="invoice_date",
     *                         type="date",
     *                         example="16 Jan 2023"
     *                      ),
     *                       @OA\Property(
     *                         property="due_date",
     *                         type="date",
     *                         example="16 Jan 2023"
     *                      ),
     *                    @OA\Property(
     *                         property="billing_address",
     *                         type="string",
     *                         example="Jungpura ext."
     *                      ),
     *                    @OA\Property(
     *                         property="notes",
     *                         type="string",
     *                         example="This invoice generate against proposal number 01"
     *                      ),
     *                      @OA\Property(
     *                         property="item_details",
     *                         type="string",
     *                         example="Legeal purposes"
     *                      ),
     *                      @OA\Property(
     *                         property="amount",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="discount",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="taxable_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="igst",
     *                         type="double",
     *                         example=09
     *                      ),
     *                      @OA\Property(
     *                         property="igst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="sgst",
     *                         type="double",
     *                         example=09
     *                      ),
     *                      @OA\Property(
     *                         property="sgst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="cgst",
     *                         type="double",
     *                         example=05
     *                      ),
     *                      @OA\Property(
     *                         property="cgst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="utgst",
     *                         type="double",
     *                         example=05
     *                      ),
     *                      @OA\Property(
     *                         property="utgst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="sub_total",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="pocket_expenses",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="expenses_details",
     *                         type="string",
     *                         example="This is the personal expenses"
     *                      ),
     *                      @OA\Property(
     *                         property="adjustment_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="total_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
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

        $invoices = Invoice::with(['audits','client'])->orderBy('id', 'DESC')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $invoices ;
        return response()->json($this->response);
    }

       /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Invoices"},
     *     path="/invoices",
     *     operationId="postInvoice",
     *     summary="create new invoice",
     *     description="Invoices",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *            @OA\Property(
     *                         property="client_id",
     *                         type="integer",
     *                         example = 1
     *                      ),
     *                      @OA\Property(
     *                         property="client_gst_number",
     *                         type="string",
     *                         example="09AKNJK4898M1V9"
     *                      ),
     *                      @OA\Property(
     *                         property="state_code",
     *                         type="integer",
     *                         example=07
     *                      ),
     *                      @OA\Property(
     *                         property="invoice_number",
     *                         type="string",
     *                         example="yr 22-23/0173"
     *                      ),
     *                      @OA\Property(
     *                         property="invoice_date",
     *                         type="date",
     *                         example="2023-01-06"
     *                      ),
     *                       @OA\Property(
     *                         property="due_date",
     *                         type="date",
     *                         example="2023-01-06"
     *                      ),
     *                    @OA\Property(
     *                         property="billing_address",
     *                         type="string",
     *                         example="Jungpura ext."
     *                      ),
     *                    @OA\Property(
     *                         property="notes",
     *                         type="string",
     *                         example="This invoice generate against proposal number 01"
     *                      ),
     *                      @OA\Property(
     *                         property="item_details",
     *                         type="string",
     *                         example="Legeal purposes"
     *                      ),
     *                      @OA\Property(
     *                         property="amount",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="discount",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="taxable_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="igst",
     *                         type="double",
     *                         example=09
     *                      ),
     *                      @OA\Property(
     *                         property="igst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="sgst",
     *                         type="double",
     *                         example=09
     *                      ),
     *                      @OA\Property(
     *                         property="sgst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="cgst",
     *                         type="double",
     *                         example=05
     *                      ),
     *                      @OA\Property(
     *                         property="cgst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="utgst",
     *                         type="double",
     *                         example=05
     *                      ),
     *                      @OA\Property(
     *                         property="utgst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="sub_total",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="pocket_expenses",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="expenses_details",
     *                         type="string",
     *                         example="This is the personal expenses"
     *                      ),
     *                      @OA\Property(
     *                         property="adjustment_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="total_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
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
            'client_id' => 'required|exists:App\Models\Company,id',
            'invoice_number'=>'required',
            'invoice_date'=>'required',
            'due_date'=>'required',
            'amount'=>'required'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        
        $invoice = new Invoice($request->all());
     
        $invoice->save();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    }
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Invoices"},
     *     path="/invoices/{invoice_id}",
     *     operationId="showInvoice",
     *     summary="create new invoice",
     *     description="Invoices",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="invoice_id", in="path", required=true, description="Invoice ID"),
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
     *                         property="client_gst_number",
     *                         type="string",
     *                         example="09AKNJK4898M1V9"
     *                      ),
     *                      @OA\Property(
     *                         property="state_code",
     *                         type="integer",
     *                         example=07
     *                      ),
     *                      @OA\Property(
     *                         property="invoice_number",
     *                         type="string",
     *                         example="yr 22-23/0173"
     *                      ),
     *                      @OA\Property(
     *                         property="invoice_date",
     *                         type="date",
     *                         example="16 Jan 2023"
     *                      ),
     *                       @OA\Property(
     *                         property="due_date",
     *                         type="date",
     *                         example="16 Jan 2023"
     *                      ),
     *                    @OA\Property(
     *                         property="billing_address",
     *                         type="string",
     *                         example="Jungpura ext."
     *                      ),
     *                    @OA\Property(
     *                         property="notes",
     *                         type="string",
     *                         example="This invoice generate against proposal number 01"
     *                      ),
     *                      @OA\Property(
     *                         property="item_details",
     *                         type="string",
     *                         example="Legeal purposes"
     *                      ),
     *                      @OA\Property(
     *                         property="amount",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="discount",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="taxable_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="igst",
     *                         type="double",
     *                         example=09
     *                      ),
     *                      @OA\Property(
     *                         property="igst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="sgst",
     *                         type="double",
     *                         example=09
     *                      ),
     *                      @OA\Property(
     *                         property="sgst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="cgst",
     *                         type="double",
     *                         example=05
     *                      ),
     *                      @OA\Property(
     *                         property="cgst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="utgst",
     *                         type="double",
     *                         example=05
     *                      ),
     *                      @OA\Property(
     *                         property="utgst_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="sub_total",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="pocket_expenses",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="expenses_details",
     *                         type="string",
     *                         example="This is the personal expenses"
     *                      ),
     *                      @OA\Property(
     *                         property="adjustment_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
     *                      @OA\Property(
     *                         property="total_amt",
     *                         type="double",
     *                         example=2000.00
     *                      ),
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
        $validator = Validator::make(['invoice_id' => $id], [
            'invoice_id' => 'required|exists:App\Models\Invoice,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        
        $invoice = Invoice::where('id',$id)->with(['audits','client'])->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $invoice;
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
    public function update(Request $request, $id)
    {
        //
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
