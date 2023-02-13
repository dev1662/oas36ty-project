<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Models\CommentMention;
use App\Models\Mailbox;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
// use Illuminate\Support\FacadesValidator;

use App\Models\Task;
use App\Models\TaskComment;
use App\Models\TaskUser;
use App\Models\UserMailbox;
use Illuminate\Support\Facades\Auth;
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
        // $user = $request->user;
        $validator = Validator::make(['task_id' => $taskID], [
            'task_id' => 'required|exists:App\Models\Task,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $original_task_id = $taskID ?? 'undefined';//$_GET['task_id'];
        // return $original_task_id;
        


            if($original_task_id != 'undefined'){

                $task = Task::find($original_task_id);
                $taskComments = $task->comments()->with([
                    'user' => function ($q) {
                        $q->select('id', 'name', 'email', 'avatar');
                    },
                    'audits',
                    'mentions'
                    ])->select('id', 'user_id', 'comment', 'created_at','parent_id')->orderBy('id', 'ASC')->get();
                    // if($original_task_id != undefined)
                    $assignedUsers = Task::where(['id' => $original_task_id])->with([
                        'users' => function ($q) {
                            $q->select('users.id', 'users.name', 'users.email', 'users.avatar');
                        },
                        // 'audits',
                        ])->orderBy('id', 'ASC')->first();
            // $taskComments['mail'] = $this->getTaskMailThread($request,$taskID);
            // return $taskComments;
                        
            }
            if($original_task_id == 'undefined'){
                if($_GET['route'] == 'leads'){
                    $route = 'lead';
                }
                
                // return $task = TaskComment::with(['user' => function ($q) {
                //     $q->select('id', 'name', 'email', 'avatar');
                // },
                // 'audits',])->select('id', 'user_id', 'comment', 'created_at')->orderBy('id', 'ASC')->get();

                 $assignedUsers = Task::where(['type' => $route])->with([
                    'users' => function ($q) {
                        $q->select('users.id', 'users.name', 'users.email', 'users.avatar');
                    },
                    // 'audits',
                    ])->orderBy('id', 'ASC')->get();
            }
        $this->response['assigned_users'] = $assignedUsers ?? [];
        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $taskComments ?? [];
        return response()->json($this->response);
    }
    public function usersAssigned()
    {
        return Task::all()->comments();
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
    public function store(Request $request)
    {
        $user = $request->user();
        // return $request->all();
        $validator = Validator::make($request->all(), [
            'task_id' => 'required|exists:App\Models\Task,id',
            'comment' => 'required',
            'user_ids' => 'array|nullable',
            'user_ids.*.id' => 'nullable|exists:App\Models\User,id',
        ]);
        $taskID = $request->task_id;
        // return $taskID;
        $user_ids = $request->user_ids;

        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $task = Task::find($taskID);
        $comment_arr = [
            'comment' => $request->comment,//trim(preg_replace("/(@\w+)/",'',$request->comment)),
            'task_id' => $request->task_id,
            'parent_id' =>$request->parent_id ?? '',
        ];
        // return $comment_arr
        $taskComment = new TaskComment($comment_arr);
        $taskComment->user_id = $user->id;
        $comment = $task->comments()->save($taskComment);
     
        $id  = $comment->id;
        if(!empty($user_ids)){
            foreach ($user_ids as $key => $users) {
                // $toDo->mentionUsers()->sync($request->mention_users[0]['id']);
                // $toDo->mentionUsers()->sync($users['id']);
                CommentMention::create([
                    'task_comment_id' => $id ?? null,
                    'user_id' => $users['id'] ?? null
                ]);
                
            }
        }
        // $taskC = TaskComment::find($id);
        
        broadcast(new MessageSent($user, $taskComment));


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
        if (!$taskComment) {
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
        if (!$taskComment) {
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

     /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"tasks"},
     *     path="/tasks/{taskID}/mail-thread",
     *     operationId="showMailThread",
     *     summary="Show Mail Thread",
     *     description="Show Task",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="taskID", in="path", required=true, description="Task ID"),
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
     *                      @OA\Property(
     *                         property="type",
     *                         type="string",
     *                         example="lead"
     *                      ),
     *                      @OA\Property(
     *                         property="subject",
     *                         type="string",
     *                         example="Task subject"
     *                      ),
     *                      @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="Task description"
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


  public function getTaskMailThread(Request $request,$id){
    $user = $request->user();
    $user_id = $user->id;
    $result = [];
    $validator = Validator::make(['task_id' => $id], [
        'task_id' => 'required|exists:App\Models\Task,id',
    ]);
    if ($validator->fails()) {
        $this->response["code"] = "INVALID";
        $this->response["message"] = $validator->errors()->first();
        $this->response["errors"] = $validator->errors();
        return response()->json($this->response, 422);
    }
    if ($id) {
        $spam_trash_id = UserMailbox::select('mailbox_id','message_id')->where( function($query){ 
         $query->where(['is_spam'=>1])->orWhere(['is_trash'=>1]);
        })->where('user_id',$user_id)
          ->get();
          $spam_trash_ids = [];
          $spamTrash_messageId = [];
          foreach($spam_trash_id as $row){
            $spam_trash_ids[] = $row->mailbox_id;
            $spamTrash_messageId[] = $row->message_id ?? '';
          }
        $results = Mailbox::where(['task_id' => $id])->where('is_parent', 1)
        ->whereNotIn('id',$spam_trash_ids)
        ->whereNotIn('message_id',$spamTrash_messageId)->with([
          'attachments_file',
        //   'userMailbox' => function ($q) use ($user_id) {
        //     $q->where(['user_id' => $user_id])->get();
        //   },
        //   'taskStatus',

        ])->get();
        // return  $results[0]['folder'];
        //  return $results;
        if(count($results) > 0){
        if($results[0]['folder'] == 'INBOX'){
        $username = $results[0]['to_email'];
        }else if($results[0]['folder'] = 'Sent Mail'){
            $username = $results[0]['from_email'];
        }else{
            $username = $results[0]['to_email'];
        }

        foreach ($results as $key => $res) {
          $eamils_arr = [];
          // if(!empty($res['in_reply_to'])){
          $eamils_arr = Mailbox::whereIn('folder', ['INBOX', 'Sent Mail'])
            ->where('message_id', '!=', $res['message_id'])
            ->where(function ($query) use ($res) {
              $query->orWhere('references', 'LIKE', '%' . $res['message_id'] . '%');
              if (!empty($res['in_reply_to'])) {
                $query->orWhere('in_reply_to', 'LIKE', '%' . $res['in_reply_to'] . '%')
                  ->orWhere('message_id', 'LIKE', '%' . $res['in_reply_to'] . '%');
              }
            })
            ->where(function ($query) use ($username) {
              $query->where(['to_email' => $username])
                ->orWhere(['from_email' => $username]);
            })->with('attachments_file')
            ->orderBy('u_date')->get();
          // }    
          if (count($eamils_arr) > 0) {
            $result[] = ['parent' => $res, 'childs' => $eamils_arr];
          } else {
            $result[] = ['parent' => $res];
          }
        }
            }

        $this->response['status'] = true;
        $this->response['message'] = 'data fetched';
        $this->response['data'] = $result ?? '';
        return response()->json($this->response);

      }


}


    /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"taskComments"},
     *     path="/mail-to-comments/{mailbox_id}",
     *     operationId="putMailToComment",
     *     summary="Update mail to Comments",
     *     description="Update Mail to Comments",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="mailbox_id", in="path", required=true, description="Mailbox ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="task_id", type="integer", example="1", description=""),
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

public function mailToComments(Request $request, $id){
    $user = $request->user();
    $user_id = $user->id;
    $result = [];
    $validator = Validator::make(['mailbox_id' => $id] + $request->all(), [
        'mailbox_id'=>'required|exists:App\Models\Mailbox,id',
        'task_id' => 'required|exists:App\Models\Task,id',
    ]);
    if ($validator->fails()) {
        $this->response["code"] = "INVALID";
        $this->response["message"] = $validator->errors()->first();
        $this->response["errors"] = $validator->errors();
        return response()->json($this->response, 422);
    }

     Mailbox::where('id',$id)->update(['task_id'=>$request->task_id]);
     $this->response['status'] = true;
        $this->response['message'] =  __('strings.update_success');
        return response()->json($this->response);

}
}
