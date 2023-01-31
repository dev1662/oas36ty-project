<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

use App\Models\ToDo;
use App\Models\ToDoMention;
use Illuminate\Support\Facades\Auth;

class ToDoController extends Controller
{
    /**
     * 
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"toDos"},
     *     path="/to-dos",
     *     operationId="getToDos",
     *     summary="ToDos",
     *     description="ToDos",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
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
     *                      @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="to_do",
     *                         type="string",
     *                         example="A To-Do"
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

    public function index(Request $request)
    {
        // $todo = [
            
        //         ["id"=> 1,
        //         "title" => 'Entire change break our wife wide it daughter mention member.',
        //         // "dueDate"=> '2020-11-25',
        //         "description"=>
        //           '<p>Chocolate cake topping bonbon jujubes donut sweet wafer. Marzipan gingerbread powder brownie bear claw. Chocolate bonbon sesame snaps jelly caramels oat cake.</p>',

        //         // "assignee"=> [
        //         //   "fullName" => 'Jacob Ramirez',
        //         //   "avatar"=> null,
        //         // ],
        //         // "tags"=> ['update'],
        //         // "isCompleted"=> false,
        //         // "isDeleted"=> false,
        //         // "isImportant"=> false,
        //         ],
              
        //     ];
            // $this->response['status'] = true;
            // $this->response['message'] =  __('strings.get_all_success');
            // $this->response['data'] = $todo;
            // return response()->json($this->response);
        $user = $request->user();

        $toDos = $user->toDos()->select('id', 'task_id','to_do', 'status')->where('status',ToDo::STATUS_NOT_DONE)->with([
            'task' => function($q){
                $q->select('id', 'type', 'subject', 'description');
            },
            'mentionUsers' => function($q){
                $q->select('users.id', 'name', 'email', 'avatar');
            },
        ])->latest()->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $toDos;
        return response()->json($this->response);
    }



    /**
     * 
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"toDos"},
     *     path="/to-dos",
     *     operationId="postToDos",
     *     summary="Create ToDo",
     *     description="Create ToDo",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="to_do", type="string", example="A ToDo", description=""),
     *             @OA\Property(property="task_id", type="integer", example="", description=""),
     *             @OA\Property(
     *                  property="user_ids", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="integer",
     *                         example="1"
     *                  ),
     *              ),
     *         )
     *     ),
     *     @OA\Response(
     *          response=200, 
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Created new record successfully"),
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
     *                  property="to_do", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected to_do is invalid."
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
        // return $request->all();
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'to_do' => 'required|array',
            'to_do.*' => 'required|max:255',
            'task_id' => 'nullable|exists:App\Models\Task,id',
            'user_ids' => 'nullable|array',
            'user_ids.*.id' => 'nullable|exists:App\Models\User,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        $todos = $request->to_do;
        $taskID = $request->task_id;
        $user_ids = $request->user_ids;
        
        // $arr = [];
        foreach ($todos as $key => $todo) {
            // preg_match_all("/(@\w+)/", $todo['subtask_assignee'], $matches);
            $real_todo = $todo;//trim(preg_replace("/(@\w+)/",'',$todo['subtask_assignee']));
        // return $matches;
        $todo_array = [
            // 'user_id' => Auth::user()->id,
            'task_id' => $taskID,
            'to_do' => $real_todo,
            // 'mention_users' => $request->mention_users
        ];
     
        $toDo = new ToDo($todo_array);
        
        $toDo->status = ToDo::STATUS_NOT_DONE;
        $user->toDos()->save($toDo);
    
        if(!empty($user_ids)){
            foreach ($user_ids as $key => $users) {
               
                // $toDo->mentionUsers()->sync($request->mention_users[0]['id']);
                // $toDo->mentionUsers()->sync($users['id']);
                ToDoMention::create([
                    'to_do_id' => $toDo->id,
                    'user_id' => $users['id']
                ]);
                
            }
        }
    }
        
        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request,$id)
    {
        // return $id;
        $user = $request->user();

        $toDos = $user->toDos()->select('id', 'task_id','to_do', 'status')->where(['status'=>ToDo::STATUS_NOT_DONE, 'task_id' => $id])->with([
            'task' => function($q){
                $q->select('id', 'type', 'subject', 'description');
            },
            'mentionUsers' => function($q){
                $q->select('users.id', 'name', 'email', 'avatar');
            },
            'audits'
        ])->latest()->get();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $toDos;
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"toDos"},
     *     path="/to-dos/{toDoID}",
     *     operationId="putToDo",
     *     summary="Update ToDo",
     *     description="Update ToDo",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="toDoID", in="path", required=true, description="To Do ID"),
     *     @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="to_do", type="string", example="A ToDo", description=""),
     *             @OA\Property(property="task_id", type="integer", example="", description=""),
     *             @OA\Property(
     *                  property="user_ids", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="integer",
     *                         example="1"
     *                  ),
     *              ),
     *             @OA\Property(property="status", type="string", example="done", description=""),
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
     *                  property="to_do_id", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected to_do_id is invalid."
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
        $user = $request->user();
        // return $id;
        $validator = Validator::make(['to_do_id' => $id] + $request->all(), [
            'to_do_id' => 'required|exists:App\Models\ToDo,id',
            'to_do' => 'required|max:255',
            'task_id' => 'nullable|exists:App\Models\Task,id',
            'user_ids' => 'nullable|array',
            'user_ids.*.id' => 'nullable|exists:App\Models\User,id',
            'status' => 'required|in:not-done,done',
        ]);
        
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        
        $toDo = $user->toDos()->find($id);
        if(!$toDo){
            $this->response["message"] = __('strings.update_failed');
            return response()->json($this->response, 422);
        }
        // return [$request->all(), $id, $request->user_ids];

        $toDo->fill($request->only(['to_do','task_id', 'status']));
        $toDo->update();
        $user_ids = $request->user_ids;
        if(!empty($user_ids)){
            foreach ($user_ids as $key => $users) {
               
                // $toDo->mentionUsers()->sync($request->mention_users[0]['id']);
                // $toDo->mentionUsers()->sync($users['id']);
                ToDoMention::create([
                    'to_do_id' => $id,
                    'user_id' => $users['id']
                ]);
                
            }
        }
        // if(!empty($request->mention_users)){

        //     $toDo->mentionUsers()->sync($request->mention_users[0]['id']);
        // }
        // $user_ids = $request->user_ids;
        // if(!empty($user_ids)){
        //     foreach ($user_ids as $key => $users) {
               
        //         // $toDo->mentionUsers()->sync($request->mention_users[0]['id']);
        //         // $toDo->mentionUsers()->sync($users['id']);
        //         ToDoMention::create([
        //             'to_do_id' => $toDo->id,
        //             'user_id' => $users['id']
        //         ]);
                
        //     }
        // }
        
        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }

    /**
     * 
     * @OA\Delete(
     *     security={{"bearerAuth":{}}},
     *     tags={"toDos"},
     *     path="/to-dos/{toDoID}",
     *     operationId="deleteToDo",
     *     summary="Delete ToDo",
     *     description="Delete ToDO",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="toDoID", in="path", required=true, description="To Do ID"),
     *     @OA\Response(
     *          response=200, 
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Record deleted successfully"),
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
     *                  property="to_do_id", 
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected to_do_id is invalid."
     *                  ),
     *              ),
     *                  ),
     *              ),
     *          )
     *     ),
     * )
     */
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        // return $id;
        
        $validator = Validator::make(['to_do_id' => $id], [
            'to_do_id' => 'required|exists:App\Models\ToDo,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $toDo = $user->toDos()->find($id);
        if(!$toDo){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        $toDo->mentions()->forceDelete();

        if ($toDo->forceDelete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
