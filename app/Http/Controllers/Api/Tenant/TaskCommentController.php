<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
// use Illuminate\Support\FacadesValidator;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Support\Facades\Validator;

class TaskCommentController extends Controller
{
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"taskComments"},
     *     path="/tasks/{taskID}/comments",
     *     operationId="getTaskComments",
     *     summary="Task Comments",
     *     description="Task Comments",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="taskID", in="path", required=true, description="Task ID"),
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
     *                  @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="user_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="comment",
     *                         type="string",
     *                         example="Task Comment"
     *                      ),
     *                        
     *                  @OA\Property(
     *                         property="created_at",
     *                         type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ), 
     *                 
     *              @OA\Property(
     *               property="user",
     *                  @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="John Doe"
     *                      ),
     *                      @OA\Property(
     *                         property="email",
     *                         type="string",
     *                         example="example@gmail.com"
     *                      ),
     *                     ),
     *              @OA\Property(
     *               property="audits",
     *                  type="array",
     *                @OA\Items(
     *                  @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="user_type",
     *                         type="string",
     *                         example="App\\Models\\User"
     *                      ),
     *                   @OA\Property(
     *                         property="user_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="event",
     *                         type="string",
     *                         example="created"
     *                      ),
     *                      @OA\Property(
     *                         property="auditable_type",
     *                         type="string",
     *                         example="App\\Models\\TaskComment"
     *                      ),
     *                       @OA\Property(
     *                         property="auditable_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="old_values",
     *                         type="string",
     *                         example="[]"
     *                      ),
     *                  @OA\Property(
     *               property="new_values",
     *                  @OA\Property(
     *                         property="comment",
     *                         type="string",
     *                         example="this is new comment"
     *                      ),
     *                      @OA\Property(
     *                         property="user_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="task_id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                       @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                     ),
     *                     @OA\Property(
     *                         property="url",
     *                         type="string",
     *                         example="http://127.0.0.1:8000/v1/tasks/2/comments"
     *                      ),
     *                       @OA\Property(
     *                         property="ip_address",
     *                         type="string",
     *                         example="127.0.0.1"
     *                      ),
     *                      @OA\Property(
     *                         property="user_agent",
     *                         type="string",
     *                         example="Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/105.0.0.0 Safari/537.36"
     *                      ),
     *                   @OA\Property(
     *                         property="tags",
     *                         type="string",
     *                         example="null"
     *                      ),
     *                      @OA\Property(
     *                         property="created_at",
     *                         type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ), 
     *                      @OA\Property(
     *                         property="updated_at",
     *                         type="timestamp",
     *                         example="2022-09-02T06:01:37.000000Z"
     *                      ), 
     *                     ),
     *                    ),
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
    public function index(Request $request, $taskID)
    {
        $validator = Validator::make(['task_id' => $taskID], [
            'task_id' => 'required|exists:App\Models\Task,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $original_task_id = $_GET['task_id'];
        $task = Task::find($original_task_id);
        $taskComments = $task->comments()->with([
            'user' => function($q){
                $q->select('id', 'name', 'email', 'avatar');
            },
            'audits',
        ])->select('id', 'user_id', 'comment','created_at')->orderBy('id', 'ASC')->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $taskComments ?? [];
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"taskComments"},
     *     path="/tasks/{taskID}/comments",
     *     operationId="postTaskComments",
     *     summary="Create Task Comment",
     *     description="Create Task Comment",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="taskID", in="path", required=true, description="Task ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="comment", type="string", example="Task comment", description=""),
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
     *                  property="task_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected task_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function store(Request $request, $taskID)
    {
        $user = $request->user();

        $validator = Validator::make(['task_id' => $_GET['task_id']] + $request->all(), [
            'task_id' => 'required|exists:App\Models\Task,id',
            'comment' => 'required',
        ]);
        $taskID = $request->task_id;
        // return $taskID;
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $task = Task::find($taskID);

        $taskComment = new TaskComment($request->all());
        $taskComment->user_id = $user->id;
        $comment = $task->comments()->save($taskComment);
        broadcast(new MessageSent($user,$comment))->toOthers();
        $this->response["status"] = true;
        $this->response["message"] = 'Message Sent';
        $this->response['data'] = $taskComment;
        return response()->json($this->response);
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
     *     tags={"taskComments"},
     *     path="/tasks/{taskID}/comments/{taskCommentID}",
     *     operationId="putTaskComment",
     *     summary="Update Task Comment",
     *     description="Update Task Comment",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="taskID", in="path", required=true, description="Task ID"),
     *     @OA\Parameter(name="taskCommentID", in="path", required=true, description="Task Comment ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="comment", type="string", example="Task comment", description=""),
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
     *                  property="task_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected task_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function update(Request $request, $taskID, $taskCommentID)
    {
        $validator = Validator::make(['task_id' => $taskID, 'task_comment_id' => $taskCommentID] + $request->all(), [
            'task_id' => 'required|exists:App\Models\Task,id',
            'task_comment_id' => 'required|exists:App\Models\TaskComment,id',
            'comment' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $task = Task::find($taskID);

        $taskComment = $task->comments()->find($taskCommentID);
        if(!$taskComment){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }

        $taskComment->fill($request->only(['comment']));
        $taskComment->update();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Delete(
     *     security={{"bearerAuth":{}}},
     *     tags={"taskComments"},
     *     path="/tasks/{taskID}/comments/{taskCommentID}",
     *     operationId="deleteTaskComment",
     *     summary="Delete Task Comment",
     *     description="Delete Task Comment",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="taskID", in="path", required=true, description="Task ID"),
     *     @OA\Parameter(name="taskCommentID", in="path", required=true, description="Task Comment ID"),
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
     *                  property="task_id",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected task_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function destroy($taskID, $taskCommentID)
    {
        $validator = Validator::make(['task_id' => $taskID, 'task_comment_id' => $taskCommentID], [
            'task_id' => 'required|exists:App\Models\Task,id',
            'task_comment_id' => 'required|exists:App\Models\TaskComment,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $task = Task::find($taskID);

        $taskComment = $task->comments()->find($taskCommentID);
        if(!$taskComment){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($taskComment->delete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
