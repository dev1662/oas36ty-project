<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use App\Models\SubtaskTemplate;
use App\Models\SubtaskTemplateBody;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SubtaskTemplateController extends Controller
{
  /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"Sub Task Template"},
     *     path="/subtask-templates",
     *     operationId="getSubtaskTemplate",
     *     summary=" Dsiplay all Subtask template",
     *     description="Subtask Template",
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
     *                         example="Template Name"
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
     *                  property="subtaskBody",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="subtask_template_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="steps_body",
     *                         type="string",
     *                         example="Template Body"
     *                      ),
     *                   @OA\Property(
     *                         property="attachment_url",
     *                         type="string",
     *                          example="https://oas36ty-files.s3.ap-south-1.amazonaws.com/email-files/Screenshot_20230117_1115531674021484.png"
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
        $template_data = SubtaskTemplate::with([
            'subtaskBody',
            'audits'
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
     *     tags={"Sub Task Template"},
     *     path="/subtask-templates",
     *     operationId="postSubtaskTemplate",
     *     summary=" Create Subtask template",
     *     description="Subtask Template",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *                      @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Template Name"
     *                      ),
     * 
     *               @OA\Property(
     *                  property="subtaskBody",
     *                  type="array",
     *                  @OA\Items(
     *                   @OA\Property(
     *                         property="steps_body",
     *                         type="string",
     *                         example="Template Body"
     *                      ),
     *                   @OA\Property(
     *                         property="attachment_url",
     *                         type="string",
     *                          example="https://oas36ty-files.s3.ap-south-1.amazonaws.com/email-files/Screenshot_20230117_1115531674021484.png"
     *                      ),
     *                    ),
     *                  ),
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
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // return $request->all();
        $template = new SubtaskTemplate($request->all());
         $template->save();
        if($request->subtaskBody ){
            foreach($request->subtaskBody as $row){
                $data_arr = [
                    'subtask_template_id' => $template->id,
                    'steps_body' => $row['steps_body'],
                    'attachment_url' => $row['attachment_url']
                ];
                SubtaskTemplateBody::create($data_arr);
            }
        }
        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    }

   
   /**
     *
     * @OA\Get(
     *    security={{"bearerAuth":{}}},
     *     tags={"Sub Task Template"},
     *     path="/subtask-templates/{templateId}",
     *     operationId="showSubtaskTemplate",
     *     summary=" Show Subtask template",
     *     description="Subtask Template",
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
     *                         property="name",
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
     *                ),
     * 
     *              @OA\Property(
     *                  property="subtaskBody",
     *                  type="array",
     *                  @OA\Items(
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                  @OA\Property(
     *                         property="subtask_template_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                   @OA\Property(
     *                         property="steps_body",
     *                         type="string",
     *                         example="Template Body"
     *                      ),
     *                   @OA\Property(
     *                         property="attachment_url",
     *                         type="string",
     *                          example="https://oas36ty-files.s3.ap-south-1.amazonaws.com/email-files/Screenshot_20230117_1115531674021484.png"
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
            'template_id' => 'required|exists:App\Models\SubtaskTemplate,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $template = SubtaskTemplate::where('id',$id)->with(['subtaskBody','audits'])->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $template;
        return response()->json($this->response);
    }

   /**
     *
     * @OA\Put(
     *   security={{"bearerAuth":{}}},
     *     tags={"Sub Task Template"},
     *     path="/subtask-templates/{templateId}",
     *     operationId="updateSubtaskTemplate",
     *     summary="Update Subtask template",
     *     description="Subtask Template",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="templateId", in="path", required=true, description="Template ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *            @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Template Name"
     *                      ),
     * 
     *               @OA\Property(
     *                  property="subtaskBody",
     *                  type="array",
     *                  @OA\Items(
     *                   @OA\Property(
     *                         property="steps_body",
     *                         type="string",
     *                         example="Template Body"
     *                      ),
     *                   @OA\Property(
     *                         property="attachment_url",
     *                         type="string",
     *                          example="https://oas36ty-files.s3.ap-south-1.amazonaws.com/email-files/Screenshot_20230117_1115531674021484.png"
     *                      ),
     *                    ),
     *                  ),
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
            'template_id' => 'required|exists:App\Models\SubtaskTemplate,id',
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $template = SubtaskTemplate::find($id);
        if(!$template){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $template->fill($request->only(['name']));
        $template->update();
        SubtaskTemplateBody::where('subtask_template_id',$id)->forceDelete();
        if($request->subtaskBody ){
            foreach($request->subtaskBody as $row){
                $data_arr = [
                    'subtask_template_id' => $id,
                    'steps_body' => $row['steps_body'],
                    'attachment_url' => $row['attachment_url']
                ];
                SubtaskTemplateBody::create($data_arr);
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
     *     tags={"Sub Task Template"},
     *     path="/subtask-templates/{templateId}",
     *     operationId="deleteSubtaskTemplate",
     *     summary="Delete Subtask template",
     *     description="Subtask Template",
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
            'template_id' => 'required|exists:App\Models\SubtaskTemplate,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        $template = SubtaskTemplate::find($id);
        if(!$template){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($template->forceDelete()) {
            SubtaskTemplateBody::where('subtask_template_id',$id)->forceDelete();
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
