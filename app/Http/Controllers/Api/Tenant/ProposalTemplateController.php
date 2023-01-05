<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\ProposalTemplate;
use App\Models\ProposalTemplateSection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ProposalTemplateController extends Controller
{

 /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Proposal Template"},
     *     path="/proposal-templates",
     *     operationId="getProposalTemplate",
     *     summary=" Dsiplay all proposal template",
     *     description="Proposal Template",
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
     *                         property="template_name",
     *                         type="string",
     *                         example="Proposal Name"
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
     *                  property="proposalTemplateSections",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="title",
     *                         type="string",
     *                         example="proposal title"
     *                      ),
     *                   @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="proposal descriptions"
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
        $template_data = ProposalTemplate::with([
            'audits',
            'proposalTemplateSection'
            ])->orderBy('id', 'DESC')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $template_data;
        return response()->json($this->response);
    }

       /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"Proposal Template"},
     *     path="/proposal-templates",
     *     operationId="postProposalTemplate",
     *     summary="Create proposal template",
     *     description="Proposal Template",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="template_name", type="string", example="Template Name", description=""),
     *              
     *              @OA\Property(
     *              property="templateSection", 
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
            'template_name' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // return $request->all();
        $template = new ProposalTemplate($request->all());
         $template->save();
        if($request->templateSection ){
            foreach($request->templateSection as $row){
                $data_arr = [
                    'proposal_template_id' => $template->id,
                    'title' => $row['title'],
                    'description' => $row['description']
                ];
                ProposalTemplateSection::create($data_arr);
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
     *     tags={"Proposal Template"},
     *     path="/proposal-templates/{templateId}",
     *     operationId="showProposalTemplate",
     *     summary="Show proposal template",
     *     description="Proposal Template",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="templateId", in="path", required=true, description="Template ID"),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Fetched data successfully"),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                 @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="template_name",
     *                         type="string",
     *                         example="Proposal Name"
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
     *                  property="proposalTemplateSections",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="title",
     *                         type="string",
     *                         example="proposal title"
     *                      ),
     *                   @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="proposal descriptions"
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
        $validator = Validator::make(['template_id' => $id], [
            'template_id' => 'required|exists:App\Models\ProposalTemplate,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $category = ProposalTemplate::where('id',$id)->with('proposalTemplateSection')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $category;
        return response()->json($this->response);
    }


    /**
     *
     * @OA\Put(
     *    security={{"bearerAuth":{}}},
     *     tags={"Proposal Template"},
     *     path="/proposal-templates/{templateId}",
     *     operationId="putProposalTemplate",
     *     summary="Update proposal template",
     *     description="Proposal Template",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="templateId", in="path", required=true, description="Template ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="template_name", type="string", example="Template name", description=""),
     *             @OA\Property(
     *              property="templateSection", 
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
        $validator = Validator::make(['template_id' => $id] + $request->all(), [
            'template_id' => 'required|exists:App\Models\ProposalTemplate,id',
            'template_name' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $template = ProposalTemplate::find($id);
        if(!$template){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $template->fill($request->only(['template_name']));
        $template->update();
        ProposalTemplateSection::where('proposal_template_id',$id)->forceDelete();
        if($request->templateSection ){
            foreach($request->templateSection as $row){
                $data_arr = [
                    'proposal_template_id' => $id,
                    'title' => $row['title'],
                    'description' => $row['description']
                ];
                ProposalTemplateSection::create($data_arr);
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
     *     tags={"Proposal Template"},
     *     path="/proposal-templates/{templateId}",
     *     operationId="deleteProposalTemplate",
     *     summary="Delete proposal template",
     *     description="Proposal Template",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="templateId", in="path", required=true, description="Template ID"),
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
        $validator = Validator::make(['template_id' => $id], [
            'template_id' => 'required|exists:App\Models\ProposalTemplate,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        $category = ProposalTemplate::find($id);
        if(!$category){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($category->forceDelete()) {
            ProposalTemplateSection::where('proposal_template_id',$id)->forceDelete();
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
