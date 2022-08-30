<?php

namespace App\Http\Controllers\Api\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Config;

use App\Models\Task;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Client;
use App\Models\ContactPerson;
use App\Models\TaskUser;
use PDO;

class TaskController extends Controller
{
    public function switchingDB($dbName)
    {
        Config::set("database.connections.mysql", [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $dbName,
            'username' => env('DB_USERNAME','root'),
            'password' => env('DB_PASSWORD',''),
            'unix_socket' => env('DB_SOCKET',''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ]);
    }
    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"tasks"},
     *     path="/tasks",
     *     operationId="getTasks",
     *     summary="Tasks",
     *     description="Tasks",
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

    public function index(Request $request)
    {
        // return "hh";

        $dbname = json_decode($request->header('currrent'))->tenant->organization->name;
        $dbname = config('tenancy.database.prefix').strtolower($dbname);
        // return   $dbname;
        $this->switchingDB($dbname);
        $tasks = Task::where('type', 'lead')->select('id', 'branch_id', 'category_id', 'client_id', 'contact_person_id','user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status','created_at')->with([
            'branch' => function($q){
                $q->select('id', 'name');
            },
            'category' => function($q){
                $q->select('id','name');
            },
            'client' => function($q){
                $q->select('id', 'name');
            },
            'contactPerson' => function($q){
                $q->select('id', 'name');
            },
            'users' => function($q){
                $q->select('users.id', 'name');
            },

            // 'priorities' => function($q){
            //     $q->select('id', 'icons');
            // },

        ])->latest()->get();
        // $user_details = CentralUser::find($)

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $tasks;
        return response()->json($this->response);
    }
    // public function leads(Request $request)
    // {
    //     return "hh";
    //     $dbname = json_decode($request->header('currrent'))->tenant->organization->name;
    //     $dbname = config('tenancy.database.prefix').strtolower($dbname);
    //     // return   $dbname;
    //     $this->switchingDB($dbname);
    //     $tasks = Task::where('type', 'lead')->select('id', 'branch_id', 'category_id', 'client_id', 'contact_person_id','user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status','created_at')->with([
    //         'branch' => function($q){
    //             $q->select('id', 'name');
    //         },
    //         'category' => function($q){
    //             $q->select('id','name');
    //         },
    //         'client' => function($q){
    //             $q->select('id', 'name');
    //         },
    //         'contactPerson' => function($q){
    //             $q->select('id', 'name');
    //         },
    //         'users' => function($q){
    //             $q->select('users.id', 'name');
    //         },
    //         // 'priorities' => function($q){
    //         //     $q->select('id', 'icons');
    //         // },

    //     ])->latest()->get();
    //     // $user_details = CentralUser::find($)

    //     $this->response["status"] = true;
    //     $this->response["message"] = __('strings.get_all_success');
    //     $this->response["data"] = $tasks;
    //     return response()->json($this->response);
    // }

    /**
     *
     * @OA\Post(
     *     security={{"bearerAuth":{}}},
     *     tags={"tasks"},
     *     path="/tasks",
     *     operationId="postTask",
     *     summary="Create Task",
     *     description="Create Task",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="branch_id", type="string", example="1", description=""),
     *             @OA\Property(property="category_id", type="string", example="1", description=""),
     *             @OA\Property(property="client_id", type="string", example="1", description=""),
     *             @OA\Property(property="contact_person_id", type="string", example="1", description=""),
     *             @OA\Property(property="type", type="string", example="lead", description=""),
     *             @OA\Property(property="subject", type="string", example="Task subject", description=""),
     *             @OA\Property(property="description", type="string", example="Task description", description=""),
     *             @OA\Property(property="due_date", type="string", example="2022-06-01", description=""),
     *             @OA\Property(property="importance", type="string", example="3", description=""),
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
     *                  property="subject",
     *                  type="array",
     *                  @OA\Items(
     *                         type="string",
     *                         example="The selected subject is invalid."
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
            'branch_id' => 'required',
            'category_id' => 'nullable',
            'client_id' => 'nullable',
            'contact_person_id' => 'nullable',
            'user_id' => 'nullable',
            'type' => 'required|in:lead,task',
            'subject' => 'required|max:255',
            'description' => 'nullable',
            'due_date' => 'required|date',
            'priority' => 'required',
            // 'selected_db' => 'required'
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }
        // return $request->branch_id['id'];
        for($i=0;$i<count($request->users);$i++){

            $task = new Task();

                $task->branch_id = $request->branch_id['id'];


                $task->category_id = $request->category_id['id'];


                $task->client_id = $request->client_id['id'];


                $task->contact_person_id = $request->contact_person_id['id'];

            $task->user_id = $request->users[$i]['id'];
            $task->type = $request->type;
            $task->subject = $request->subject;
            $task->description = $request->description;
            $task->due_date = $request->due_date;

                $task->priority = $request->priority['id'];


            // echo '<pre>';print_r($task);exit;
            $task->status = Task::STATUS_OPEN;
            // return $request->users[$i];
            $task->save();

            $taskss = Task::find($task->id);

            $taskUser = $taskss->users()->find($request->users[$i]['id']);
            // if($taskUser){
            //     $this->response["message"] = __('strings.store_failed');
            //     return response()->json($this->response);
            // }

            $taskss->users()->attach($request->users[$i]['id']);
        }

        $data = [
            'type' => 'dont_delete',
        ];
        $branch = Branch::where(['id' => $request->branch_id])->update($data);
        $Category = Category::where(['id' => $request->category_id])->update($data);
        $Client = Client::where(['id' => $request->client_id])->update($data);
        $ContactPerson = ContactPerson::where(['id' => $request->contact_person_id])->update($data);


        $this->response["status"] = true;
        $this->response["message"] = __('strings.store_success');
        return response()->json($this->response);
    }


    /**
     *
     * @OA\Get(
     *     security={{"bearerAuth":{}}},
     *     tags={"tasks"},
     *     path="/tasks/{taskID}",
     *     operationId="showTask",
     *     summary="Show Task",
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

        $task = Task::select('id', 'branch_id', 'category_id', 'client_id', 'contact_person_id', 'type', 'subject', 'description', 'due_date', 'importance', 'status')->with([
            'branch' => function($q){
                $q->select('id', 'name');
            },
            'category' => function($q){
                $q->select('id', 'name');
            },
            'client' => function($q){
                $q->select('id', 'name');
            },
            'contactPerson' => function($q){
                $q->select('id', 'name');
            }
        ])->find($id);

        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_one_success');
        $this->response["data"] = $task;
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Put(
     *     security={{"bearerAuth":{}}},
     *     tags={"tasks"},
     *     path="/tasks/{taskID}",
     *     operationId="putTask",
     *     summary="Update Task",
     *     description="Update Task",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="taskID", in="path", required=true, description="Task ID"),
     *     @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="branch_id", type="string", example="1", description=""),
     *             @OA\Property(property="category_id", type="string", example="1", description=""),
     *             @OA\Property(property="client_id", type="string", example="1", description=""),
     *             @OA\Property(property="contact_person_id", type="string", example="1", description=""),
     *             @OA\Property(property="type", type="string", example="lead", description=""),
     *             @OA\Property(property="subject", type="string", example="Task subject", description=""),
     *             @OA\Property(property="description", type="string", example="Task description", description=""),
     *             @OA\Property(property="due_date", type="string", example="2022-06-01", description=""),
     *             @OA\Property(property="importance", type="string", example="3", description=""),
     *             @OA\Property(property="status", type="string", example="closed", description=""),
     *         )
     *     ),
     *     @OA\Response(
     *          response=200,
     *          description="Successful Response",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Updated successfully!"),
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

    public function update(Request $request, $id)
    {
        $validator = Validator::make(['task_id' => $id] + $request->all(), [
            'task_id' => 'required|exists:App\Models\Task,id',
            'branch_id' => 'required',
            'category_id' => 'nullable',
            'client_id' => 'nullable',
            'contact_person_id' => 'nullable',
            'user_id' => 'nullable',

            'type' => 'required|in:lead,task',
            'subject' => 'required|max:255',
            'description' => 'nullable',
            'due_date' => 'required|date',
            'priority' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        // UPDATE TASK TABLE


        if(gettype($request->status) === 'array'){
            $updateTask = Task::where(['id' => $id])->update([
                'subject' => $request->subject,
                'description' => $request->description,
                'branch_id' => $request->branch_id['id'],
                'client_id' => $request->client_id['id'],
                'category_id' => $request->category_id['id'],
                'contact_person_id' => $request->contact_person_id['id'],
                'type' => $request->type,
                'due_date' => $request->due_date,
                // 'priority' => $request->priority['id'],
                'status' => $request->status['status']
            ]);

        }
        // return $request->status['status'];
        if(gettype($request->status) === 'string'){
            $updateTask = Task::where(['id' => $id])->update([
                'subject' => $request->subject,
                'description' => $request->description,
                'branch_id' => $request->branch_id['id'],
                'client_id' => $request->client_id['id'],
                'category_id' => $request->category_id['id'],
                'contact_person_id' => $request->contact_person_id['id'],
                'type' => $request->type,
                'due_date' => $request->due_date,
                // 'priority' => $request->priority['id'],
                'status' => $request->status
            ]);

        }


        // UPDATE FOREIGN KEY TABLES


        $branch = Branch::where(['id' => $request->branch_id['id']])->update(['type' => 'dont_delete']);
        $Category = Category::where(['id' => $request->category_id['id']])->update(['type' => 'dont_delete']);
        $Client = Client::where(['id' => $request->client_id['id']])->update(['type' => 'dont_delete']);
        $ContactPerson = ContactPerson::where(['id' => $request->contact_person_id['id']])->update(['type' => 'dont_delete']);


        $checkNewUser = array();
        for ($i=0; $i < count($request->users); $i++) {
            $checkNewUser = TaskUser::where(['user_id' =>  $request->users[$i]['id'], 'task_id' => $id])->get();
        }
        $user_values = [];
        foreach($checkNewUser as $newUser){

            $user_values[] =  $newUser->user_id;
        }
        // insert newly added data

        foreach($request->users as $input_users){
            if(!in_array($input_users['id'], $user_values)){
                $new_taskUser = new TaskUser();
                $new_taskUser->task_id = $id;
                $new_taskUser->user_id = $input_users['id'];
            }
        }

        // delete added user values

        foreach($user_values as $user_row){
            // return 'hh';
            $actual_array = [];
            foreach($request->users as $use){
                $actual_array[] = $use['id'];
            }

            if(!in_array($user_row, $actual_array)){
                return "user not present";
                $delete_task_user = TaskUser::where(['user_id' => $user_row])->delete();
            }
        }

        // return [
        //     'data' => $request->all(),
        //     'count' => count($checkNewUser)
        // ];
        // if($checkNewUser)
        // $task = Task::find($id);
        // if(!$task){
        //     $this->response["message"] = __('strings.update_failed');
        //     return response()->json($this->response, 422);
        // }

        // $task->fill($request->only(['task_id', 'branch_id', 'category_id', 'client_id', 'contact_person_id','user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status']));
        // $task->update();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        return response()->json($this->response);
    }

    /**
     *
     * @OA\Delete(
     *     security={{"bearerAuth":{}}},
     *     tags={"tasks"},
     *     path="/tasks/{taskID}",
     *     operationId="deleteTask",
     *     summary="Delete Task",
     *     description="Delete Task",
     *     @OA\Parameter(ref="#/components/parameters/tenant--header"),
     *     @OA\Parameter(name="taskID", in="path", required=true, description="Task ID"),
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

    public function destroy($id)
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

        $task = Task::find($id);
        if(!$task){
            $this->response["message"] = __('strings.destroy_failed');
            return response()->json($this->response, 422);
        }

        if ($task->delete()) {
            $this->response["status"] = true;
            $this->response["message"] = __('strings.destroy_success');
            return response()->json($this->response);
        }

        $this->response["message"] = __('strings.destroy_failed');
        return response()->json($this->response, 422);
    }
}
