<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalFees;
use App\Models\ProposalSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProposalController extends Controller
{
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Proposal"},
     *     path="/proposal",
     *     operationId="getProposal",
     *     summary=" Dsiplay all proposal",
     *     description="Proposal",
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
     *  @OA\Property(
     *                         property="task_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="client_name",
     *                         type="string",
     *                         example="Client Name"
     *                      ),
     *  @OA\Property(
     *                         property="concerned_person",
     *                         type="string",
     *                         example="Concerned Person"
     *                      ),
     *  @OA\Property(
     *                         property="address",
     *                         type="string",
     *                         example="Address"
     *                      ),
     *  @OA\Property(
     *                         property="subject",
     *                         type="string",
     *                         example="Subject"
     *                      ),
     *  @OA\Property(
     *                         property="prephase",
     *                         type="string",
     *                         example="Prephase"
     *                      ),
     *  @OA\Property(
     *                         property="internal_notes",
     *                         type="string",
     *                         example="Internal Notes"
     *                      ),
     *  @OA\Property(
     *                         property="footer_title",
     *                         type="string",
     *                         example="Footer Title"
     *                      ),
     *  @OA\Property(
     *                         property="footer_description",
     *                         type="string",
     *                         example="Footer Description"
     *                      ),
     * @OA\Property(
     *                         property="proposal_date",
     *                         type="date",
     *                         example="2022-09-02"
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
     *                  property="proposalSections",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="proposal_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="title",
     *                         type="string",
     *                         example="title"
     *                      ),
     *                   @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="descriptions"
     *                      ),
     *                    ),
     *                  ),
     * 
     *                  @OA\Property(
     *                  property="proposalFees",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
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
     *                         property="description",
     *                         type="string",
     *                         example="Amount descriptions"
     *                      ),
     *                   @OA\Property(
     *                         property="amount",
     *                         type="double",
     *                         example="4000.00"
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
        $proposal_data = Proposal::with([
            'proposalSection',
            'proposalFees',
            'audits'
            ])->orderBy('id', 'DESC')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $proposal_data;
        return response()->json($this->response);
    }
/**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Proposal"},
     *     path="/proposal",
     *     operationId="postProposal",
     *     summary="Create proposal",
     *     description="Proposal",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="task_id", type="integer", example="1", description=""),
     *             @OA\Property(property="proposal_date", type="date", example="2023-01-06", description=""),
     *             @OA\Property(property="client_name", type="string", example="Client name", description=""),
     *             @OA\Property(property="concerned_person", type="string", example="Concerned person ", description=""),
     *             @OA\Property(property="address", type="string", example="Address", description=""),
     *             @OA\Property(property="subject", type="string", example="Subject", description=""),
     *             @OA\Property(property="prephase", type="string", example="Prephase", description=""),
     *             @OA\Property(property="footer_title", type="string", example="Footer title", description=""),
     *             @OA\Property(property="footer_description", type="string", example="Footer description", description=""),
     *             @OA\Property(property="internal_notes", type="string", example="Internal notes", description=""),
     *              
     *              @OA\Property(
     *              property="proposalSection", 
     *              type="array",
     *              @OA\Items(
     *                @OA\property(
     *                         property = "title",
     *                         type="string",
     *                         example="Template title"
     *                  ),
     *                  @OA\property(
     *                         property = "description",
     *                         type="string",
     *                         example="Template description"
     *                  ),
     *          ),
     *        ),
     * 
     * 
     *              @OA\Property(
     *              property="proposalFees", 
     *              type="array",
     *              @OA\Items(
     *               
     *                  @OA\property(
     *                         property = "description",
     *                         type="string",
     *                         example="Amount description"
     *                  ),
     *               @OA\property(
     *                         property = "amount",
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
            'client_name' => 'required',
            'proposal_date' => 'required',
            'task_id' => 'required|exists:App\Models\Task,id',
            'concerned_person' => 'required',
            'address' => 'required',
            'subject' => 'required',
            'prephase' => 'required',
            'footer_title' => 'required',
            'footer_description' => 'required',
            'internal_notes' => 'required'
           

        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // return $request->all();
        $proposal = new Proposal($request->all());
         $proposal->save();
        if($request->proposalSection ){
            foreach($request->proposalSection as $row){
                $data_arr = [
                    'proposal_id' => $proposal->id,
                    'title' => $row['title'],
                    'description' => $row['description']
                ];
                ProposalSection::create($data_arr);
            }
        }

        if($request->proposalFees ){
            foreach($request->proposalFees as $row){
                $data_arr = [
                    'proposal_id' => $proposal->id,
                    'description' => $row['description'],
                    'amount' => $row['amount'],
                ];
                ProposalFees::create($data_arr);
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
     *     tags={"Proposal"},
     *     path="/proposal/{proposalId}",
     *     operationId="showProposal",
     *     summary="Show proposal",
     *     description="Proposal",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="proposalId", in="path", required=true, description="Proposal ID"),
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
     *  @OA\Property(
     *                         property="task_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="client_name",
     *                         type="string",
     *                         example="Client Name"
     *                      ),
     *  @OA\Property(
     *                         property="concerned_person",
     *                         type="string",
     *                         example="Concerned Person"
     *                      ),
     *  @OA\Property(
     *                         property="address",
     *                         type="string",
     *                         example="Address"
     *                      ),
     *  @OA\Property(
     *                         property="subject",
     *                         type="string",
     *                         example="Subject"
     *                      ),
     *  @OA\Property(
     *                         property="prephase",
     *                         type="string",
     *                         example="Prephase"
     *                      ),
     *  @OA\Property(
     *                         property="internal_notes",
     *                         type="string",
     *                         example="Internal Notes"
     *                      ),
     *  @OA\Property(
     *                         property="footer_title",
     *                         type="string",
     *                         example="Footer Title"
     *                      ),
     *  @OA\Property(
     *                         property="footer_description",
     *                         type="string",
     *                         example="Footer Description"
     *                      ),
     * @OA\Property(
     *                         property="proposal_date",
     *                         type="date",
     *                         example="2022-09-02"
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
     *                  property="proposalSections",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="proposal_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="title",
     *                         type="string",
     *                         example="title"
     *                      ),
     *                   @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="descriptions"
     *                      ),
     *                    ),
     *                  ),
     * 
     *                  @OA\Property(
     *                  property="proposalFees",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
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
     *                         property="description",
     *                         type="string",
     *                         example="Amount descriptions"
     *                      ),
     *                   @OA\Property(
     *                         property="amount",
     *                         type="double",
     *                         example="4000.00"
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
        $validator = Validator::make(['proposal_id' => $id], [
            'proposal_id' => 'required|exists:App\Models\Proposal,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $category = Proposal::where('id',$id)->with([
            'proposalSection',
            'proposalFees'
            ])->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $category;
        return response()->json($this->response);
    }


    
    /**
     *
     * @OA\Put(
     *    security={{"bearerAuth":{}}},
     *     tags={"Proposal"},
     *     path="/proposal/{proposalId}",
     *     operationId="putProposal",
     *     summary="Update proposal",
     *     description="Proposal",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="proposalId", in="path", required=true, description="Proposal ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="task_id", type="integer", example="1", description=""),
     *             @OA\Property(property="proposal_date", type="date", example="2023-01-06", description=""),
     *             @OA\Property(property="client_name", type="string", example="Client name", description=""),
     *             @OA\Property(property="concerned_person", type="string", example="Concerned person ", description=""),
     *             @OA\Property(property="address", type="string", example="Address", description=""),
     *             @OA\Property(property="subject", type="string", example="Subject", description=""),
     *             @OA\Property(property="prephase", type="string", example="Prephase", description=""),
     *             @OA\Property(property="footer_title", type="string", example="Footer title", description=""),
     *             @OA\Property(property="footer_description", type="string", example="Footer description", description=""),
     *             @OA\Property(property="internal_notes", type="string", example="Internal notes", description=""),
     *              
     *              @OA\Property(
     *              property="proposalSection", 
     *              type="array",
     *              @OA\Items(
     *                @OA\property(
     *                         property = "title",
     *                         type="string",
     *                         example="Template title"
     *                  ),
     *                  @OA\property(
     *                         property = "description",
     *                         type="string",
     *                         example="Template description"
     *                  ),
     *          ),
     *        ),
     * 
     * 
     *              @OA\Property(
     *              property="proposalFees", 
     *              type="array",
     *              @OA\Items(
     *               
     *                  @OA\property(
     *                         property = "description",
     *                         type="string",
     *                         example="Amount description"
     *                  ),
     *               @OA\property(
     *                         property = "amount",
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
        $validator = Validator::make(['proposal_id' => $id] + $request->all(), [
            'proposal_id' => 'required|exists:App\Models\Proposal,id',
            'client_name' => 'required',
            'proposal_date' => 'required',
            'task_id' => 'required|exists:App\Models\Task,id',
            'concerned_person' => 'required',
            'address' => 'required',
            'subject' => 'required',
            'prephase' => 'required',
            'footer_title' => 'required',
            'footer_description' => 'required',
            'internal_notes' => 'required'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $proposal = Proposal::find($id);
        if(!$proposal){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $proposal->fill($request->only(['client_name','proposal_date','task_id','concerned_person','address','subject','prephase','footer_title','footer_description','internal_notes']));
        $proposal->update();
        ProposalSection::where('proposal_id',$id)->forceDelete();
        if($request->proposalSection ){
            foreach($request->proposalSection as $row){
                $data_arr = [
                    'proposal_id' => $proposal->id,
                    'title' => $row['title'],
                    'description' => $row['description']
                ];
                ProposalSection::create($data_arr);
            }
        }
        ProposalFees::where('proposal_id',$id)->forceDelete();

        if($request->proposalFees ){
            foreach($request->proposalFees as $row){
                $data_arr = [
                    'proposal_id' => $proposal->id,
                    'description' => $row['description'],
                    'amount' => $row['amount'],
                ];
                ProposalFees::create($data_arr);
            }
        }
       
        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }


 /**
     *
     * @OA\Delete(
     *    security={{"bearerAuth":{}}},
     *     tags={"Proposal"},
     *     path="/proposal/{proposalId}",
     *     operationId="deleteProposal",
     *     summary="Delete proposal",
     *     description="Proposal",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="proposalId", in="path", required=true, description="Proposal ID"),
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
        $validator = Validator::make(['proposal_id' => $id], [
            'proposal_id' => 'required|exists:App\Models\Proposal,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        $proposal = Proposal::find($id);
        if(!$proposal){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($proposal->forceDelete()) {
            ProposalSection::where('proposal_id',$id)->forceDelete();
            ProposalFees::where('proposal_id',$id)->forceDelete();

            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
