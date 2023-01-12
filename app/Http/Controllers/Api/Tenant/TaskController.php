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
use App\Models\Company;
use App\Models\ContactPerson;
use App\Models\Mailbox;
use App\Models\TaskComment;
use App\Models\TaskUser;
use App\Models\User;
use DateTime;
use PDO;

class TaskController extends Controller
{

    public function convert_lead_to_task(Request $req, $id)
    {
        Task::where('id', $id)->update([
            'type' => 'task'
        ]);
        $this->response['status'] = true;
        $this->response['message'] = 'Lead converted to task';

        // $this->response["data"] = $tasks;
        return response()->json($this->response);
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
        $route= $_GET['route'];
        if($route == 'leads' || $route == 'leads-inner'){
            $route = 'lead';
        }elseif($route == 'tasks' || $route == 'tasks-inner'){
            $route= 'task';
        }

        
        $dbname = $request->header('X-Tenant');
        $dbname = config('tenancy.database.prefix').strtolower($dbname);
        // return   $dbname;
        $this->switchingDB($dbname);
    
            $tasks = Task::where('type', $route)->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status_master_id', 'created_at')->with([
                'selfUser',
                'branch' => function ($q) {
                    $q->select('id', 'name');
                },
                'category' => function ($q) {
                    $q->select('id', 'name');
                },
                'Company' => function ($q) {
                    $q->select('id', 'name');
                },
                'contactPerson' => function ($q) {
                    $q->select('id', 'name');
                },
                'users' => function ($q) {
                    $q->select('users.id', 'name','avatar');
                },
                'comments' => function($q){
                    $q->select('id', 'comment', 'task_id', 'user_id', 'created_at');
                },
                'status_master',
                'audits',
                // 'priorities' => function($q){
                //     $q->select('id', 'icons');
                // },

            ])->latest()->get();
        
        // $user_details = CentralUser::find($)

        $this->response["status"] = true;
        if($_GET['route'] == 'leads'){

            $this->response["message"] = 'Leads Fetched';
        }
        if($_GET['route'] == 'tasks'){

            $this->response["message"] = 'Tasks Fetched';
        }
        $this->response["data"] = $tasks;
        return response()->json($this->response);
    }
    public function filterData2(Request $request)
    {
        // return $request->all();
        $route= $_GET['route'];
        if($route == 'leads'){
            $route = 'lead';
        }elseif($route == 'tasks'){
            $route= 'task';
        }
        
        $filters = [
            'branch' => $request->input('branch') ?? '',
            'category' => $request->input('category') ?? '',
            'company' => $request->input('company') ?? '',
            'contact' => $request->input('contact') ?? '',
            'priority' => $request->input('priority')['id'] ?? '',
            'search' => $request->input('search') ?? '',
            'status' => strtolower($request->input('status')) ?? '',
            'route' => $route,
            'user' => $request->input('user') ?? ''
        ];
       
            if($filters['branch']){

                $filters['branch'] =  Branch::where('name','LIKE','%'.$filters['branch'].'%')->first()['id'];
            }
            if($filters['category']){

                $filters['category'] =  Category::where('name','LIKE','%'.$filters['category'].'%')->first()['id'];
            }
            if($filters['company']){

                $filters['company'] =  Company::where('name','LIKE','%'.$filters['company'].'%')->first()['id'];
            }
            if($filters['contact']){

                $filters['contact'] =  ContactPerson::where('name','LIKE','%'.$filters['contact'].'%')->first()['id'];
            }
            if($filters['user']){

                $filters['user'] =  User::where('name','LIKE','%'.$filters['user'].'%')->first()['id'];
            }


        // return $filters;
        // $tasks = Task::when($filters, function($query) use ($filters){
        //     return $query->where(function ($query) use ($filters) { // group these 'Where' and 'orWhere'
        //         $query->where('status', strtolower($filters['status']))
        //               ->orWhere('priority', $filters['priority'])
        //               ->orWhere('type', $filters['route']);
        //             });
        // })


        
        $tasks = Task::
                   where('type', $route)
                //    ->where('business_type','!=',3)
                //    ->where('parent_id','=',null)
                   ->where(function($query) use ($filters){
                       if(!empty($filters['status'])){
                        $query

                        ->where('status_master_id','LIKE','%'.$filters['status'].'%');
                    }

                    if(!empty($filters['priority'])){
                        $query

                        ->where('priority',$filters['priority']);
                    }   if(!empty($filters['branch'])){
                        $query

                        ->where('branch_id',$filters['branch']);
                    }   if(!empty($filters['category'])){
                        $query

                        ->where('category_id',$filters['category']);
                    }

                    if(!empty($filters['company'])){
                        $query
                        ->where('company_id',$filters['company']);
                    }
                    if(!empty($filters['contact'])){
                        $query
                        ->where('contact_person_id',$filters['contact']);
                    }
                    // if(!empty($filters['search'])){
                    //     $query
                    //     // ->where('description','LIKE','%'.$filters['search'].'%')
                    //     ->where('subject','LIKE','%'.$filters['search'].'%');
                    //     // ->where(function($query) use($filters){
                    //     //     if(!empty($filters['search'])){
                    //     //         $query->where('subject','LIKE','%'.$filters['search'].'%');
                    //     //     }
                    //     //     if(!empty($filters['search'])){
                    //     //         $query->where('description','LIKE','%'.$filters['search'].'%');
                    //     //     }
                    //     // });

                    //     // ->where('description','LIKE','%'.$filters['search'].'%');
                    // }
                    if(!empty($filters['search'])){
                        $search = $filters['search'];

                        $query->where(function($query) use($search){

                            $query->where('description','LIKE','%'.$search.'%')
                            
                            ->orWhere('subject', 'LIKE', '%'.$search.'%');
                        });
                    }
                        //    ->where('priority', 'LIKE', '%'.$filters['priority'].'%')
                        //    ->where('branch_id', $filters['branch'])
                        //    ->where('category_id', $filters['category'])
                        //    ->where('company_id', 'LIKE', '%'.$filters['company'].'%')
                        //    ->where('contact_person_id', 'LIKE', '%'.$filters['contact'].'%')
                        //    ->where('subject', 'LIKE', '%'.$filters['search'].'%')
                        //    ->where('description', 'LIKE', '%'.$filters['search'].'%');

                           
                        //    ->orWhere('company_name', 'LIKE', '%'.$filters.'%')
                        //    ->orWhere('email', 'LIKE', '%'.$filters.'%')
                        //    ->orWhere('mobile', 'LIKE', '%'.$filters.'%');
                       })->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status_master_id', 'created_at')->with([
                        'branch' => function ($q) {
                            $q->select('id', 'name');

                        },
                        'category' => function ($q) {
                            $q->select('id', 'name');
                        },
                        'Company' => function ($q) {
                            $q->select('id', 'name');
                        },
                        'contactPerson' => function ($q) {
                            $q->select('id', 'name');
                        },
                        'users' => function ($q) use($filters) {
                            
                            $q->where('users.id', 'LIKE', '%'. $filters['user']. '%')->select('users.id', 'name', 'avatar');
                        },
                        'audits',
                        // 'priorities' => function($q){
                        //     $q->select('id', 'icons');
                        // },
            
                    ])
                    //    ->orderBy('created_at', 'desc')
                       ->get();
                    //    return $tasks;
        // return Task::where('contact_person_id', 'LIKE', '%'.$filters['contact'].'%')->first();
        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $tasks ?? [];
        return response()->json($this->response);
        // if ($request->status) {


        //     $tasks = Task::where(['status' => strtolower($request->status), 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->company){
        //     $tasks = Task::where(['company_id' => $request->company['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->priority){
        //     // return $request->priority['id'];
        //     $tasks = Task::where(['priority' =>  $request->priority['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->category){
        //     $tasks = Task::where(['category_id' =>  $request->category['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->contact){
        //     $tasks = Task::where(['contact_person_id'=> $request->contact['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // $search = $request->search ?? null;
        // if($search){
            
        //     $tasks = Task::where(['subject'=>$search, 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($search == null){
        //     $tasks = Task::where([ 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->branch){
        //     $tasks = Task::where(['branch_id' => $request->branch['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->user){
        //     // return $request->user['id'];
        //     $tasks = TaskUser::where(['user_id' => $request->user['id']])->select('id','task_id', 'user_id', 'created_at')->with([
        //         'tasks' => function ($q) {
        //             $q->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at');
        //         },
                
        //         // 'users' => function ($q) {
        //         //     $q->select('users.id', 'name');
        //         // },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        //     // $tasks = Task::where('subject', 'like', '%'. $request->search . '%')->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //     //     'branch' => function ($q) {
        //     //         $q->select('id', 'name');
        //     //     },
        //     //     'category' => function ($q) {
        //     //         $q->select('id', 'name');
        //     //     },
        //     //     'Company' => function ($q) {
        //     //         $q->select('id', 'name');
        //     //     },
        //     //     'contactPerson' => function ($q) {
        //     //         $q->select('id', 'name');
        //     //     },
        //     //     'users' => function ($q) {
        //     //         $q->select('users.id', 'name');
        //     //     },
        //     //     'audits',
        //     //     // 'priorities' => function($q){
        //     //     //     $q->select('id', 'icons');
        //     //     // },

        //     // ])->latest()->get();
        // }
       
        // $this->response["status"] = true;
        // $this->response["message"] = __('strings.get_all_success');
        // $this->response["data"] = $tasks ?? [];
        // return response()->json($this->response);
    }
    public function filterData(Request $request)
    {
        // return $request->all();
        $route= $_GET['route'];
        if($route == 'leads'){
            $route = 'lead';
        }elseif($route == 'tasks'){
            $route= 'task';
        }
        
        $filters = [
            'branch' => $request->input('branch') ?? '',
            'category' => $request->input('category') ?? '',
            'company' => $request->input('company') ?? '',
            'contact' => $request->input('contact') ?? '',
            'priority' => $request->input('priority')['id'] ?? '',
            'search' => $request->input('search') ?? '',
            'status' => strtolower($request->input('status')) ?? '',
            'route' => $route,
            'user' => $request->input('user') ?? ''
        ];
       
            if($filters['branch']){

                $filters['branch'] =  Branch::where('name','LIKE','%'.$filters['branch'].'%')->first()['id'];
            }
            if($filters['category']){

                $filters['category'] =  Category::where('name','LIKE','%'.$filters['category'].'%')->first()['id'];
            }
            if($filters['company']){

                $filters['company'] =  Company::where('name','LIKE','%'.$filters['company'].'%')->first()['id'];
            }
            if($filters['contact']){

                $filters['contact'] =  ContactPerson::where('name','LIKE','%'.$filters['contact'].'%')->first()['id'];
            }
            // if($filters['user']){

            //     $filters['user'] =  User::where('name','LIKE','%'.$filters['user'].'%')->first()['n'];
            // }


        // return $filters;
        // $tasks = Task::when($filters, function($query) use ($filters){
        //     return $query->where(function ($query) use ($filters) { // group these 'Where' and 'orWhere'
        //         $query->where('status', strtolower($filters['status']))
        //               ->orWhere('priority', $filters['priority'])
        //               ->orWhere('type', $filters['route']);
        //             });
        // })

            

        $tasks = Task::
                   where('type', $route)
                //    ->where('business_type','!=',3)
                //    ->where('parent_id','=',null)
                   ->where(function($query) use ($filters){
                    //    if(!empty($filters['status'])){
                    //     $query

                    //     ->where('status','LIKE','%'.$filters['status'].'%');
                    // }

                    if(!empty($filters['priority'])){
                        $query

                        ->where('priority',$filters['priority']);
                    }   if(!empty($filters['branch'])){
                        $query

                        ->where('branch_id',$filters['branch']);
                    }   if(!empty($filters['category'])){
                        $query

                        ->where('category_id',$filters['category']);
                    }

                    if(!empty($filters['company'])){
                        $query
                        ->where('company_id',$filters['company']);
                    }
                    if(!empty($filters['contact'])){
                        $query
                        ->where('contact_person_id',$filters['contact']);
                    }
                    // if(!empty($filters['search'])){
                    //     $query
                    //     // ->where('description','LIKE','%'.$filters['search'].'%')
                    //     ->where('subject','LIKE','%'.$filters['search'].'%');
                    //     // ->where(function($query) use($filters){
                    //     //     if(!empty($filters['search'])){
                    //     //         $query->where('subject','LIKE','%'.$filters['search'].'%');
                    //     //     }
                    //     //     if(!empty($filters['search'])){
                    //     //         $query->where('description','LIKE','%'.$filters['search'].'%');
                    //     //     }
                    //     // });

                    //     // ->where('description','LIKE','%'.$filters['search'].'%');
                    // }
                    if(!empty($filters['search'])){
                        $search = $filters['search'];

                        $query->where(function($query) use($search){

                            $query->where('description','LIKE','%'.$search.'%')
                            ->orWhereHas('comments', function($q) use($search){
                                $q->where('comment', 'LIKE', '%'.$search.'%');
                            })
                            // ->comments()->orWhere('comment','LIKE', '%'.$search.'%')
                            ->orWhere('subject', 'LIKE', '%'.$search.'%');
                        });
                    }
                        //    ->where('priority', 'LIKE', '%'.$filters['priority'].'%')
                        //    ->where('branch_id', $filters['branch'])
                        //    ->where('category_id', $filters['category'])
                        //    ->where('company_id', 'LIKE', '%'.$filters['company'].'%')
                        //    ->where('contact_person_id', 'LIKE', '%'.$filters['contact'].'%')
                        //    ->where('subject', 'LIKE', '%'.$filters['search'].'%')
                        //    ->where('description', 'LIKE', '%'.$filters['search'].'%');

                           
                        //    ->orWhere('company_name', 'LIKE', '%'.$filters.'%')
                        //    ->orWhere('email', 'LIKE', '%'.$filters.'%')
                        //    ->orWhere('mobile', 'LIKE', '%'.$filters.'%');
                       })->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status_master_id', 'created_at')->with([
                        'branch' => function ($q) {
                            $q->select('id', 'name');

                        },
                        'category' => function ($q) {
                            $q->select('id', 'name');
                        },
                        'Company' => function ($q) {
                            $q->select('id', 'name');
                        },
                        'contactPerson' => function ($q) {
                            $q->select('id', 'name');
                        },
                        'users' => function ($q) use($filters) {
                            
                            $q->where('users.name', 'LIKE', '%'. $filters['user']. '%')->select('users.id', 'name', 'avatar');
                        },
                        'status_master' => function($q) use($filters){
                            $q->where('type', 'LIKE', '%' . $filters['status'].'%')->select('id', 'type');
                        },
                        'comments' ,
                        // => function($q) use($filters){
                        //     if(!empty($filters['search'])){
                        //         $search = $filters['search'];

                        //         $q->where(function($q) use($search){
                        //         $q->where('comment', 'LIKE', '%' . $search. '%')->select('id', 'comment', 'task_id', 'user_id');
                        //         });
                        //     }
                        // },
                        'audits',
                        // 'priorities' => function($q){
                        //     $q->select('id', 'icons');
                        // },
            
                    ])->latest()
                    //    ->orderBy('created_at', 'desc')
                       ->get();
                    //    return $tasks;
        // return Task::where('contact_person_id', 'LIKE', '%'.$filters['contact'].'%')->first();
        $this->response["status"] = true;
        $this->response["message"] = __('strings.get_all_success');
        $this->response["data"] = $tasks ?? [];
        return response()->json($this->response);
        // if ($request->status) {


        //     $tasks = Task::where(['status' => strtolower($request->status), 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->company){
        //     $tasks = Task::where(['company_id' => $request->company['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->priority){
        //     // return $request->priority['id'];
        //     $tasks = Task::where(['priority' =>  $request->priority['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->category){
        //     $tasks = Task::where(['category_id' =>  $request->category['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->contact){
        //     $tasks = Task::where(['contact_person_id'=> $request->contact['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // $search = $request->search ?? null;
        // if($search){
            
        //     $tasks = Task::where(['subject'=>$search, 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($search == null){
        //     $tasks = Task::where([ 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->branch){
        //     $tasks = Task::where(['branch_id' => $request->branch['id'], 'type' => $route])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //         'branch' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'category' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'Company' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'contactPerson' => function ($q) {
        //             $q->select('id', 'name');
        //         },
        //         'users' => function ($q) {
        //             $q->select('users.id', 'name');
        //         },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        // }
        // if($request->user){
        //     // return $request->user['id'];
        //     $tasks = TaskUser::where(['user_id' => $request->user['id']])->select('id','task_id', 'user_id', 'created_at')->with([
        //         'tasks' => function ($q) {
        //             $q->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at');
        //         },
                
        //         // 'users' => function ($q) {
        //         //     $q->select('users.id', 'name');
        //         // },
        //         'audits',
        //         // 'priorities' => function($q){
        //         //     $q->select('id', 'icons');
        //         // },

        //     ])->latest()->get();
        //     // $tasks = Task::where('subject', 'like', '%'. $request->search . '%')->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status', 'created_at')->with([
        //     //     'branch' => function ($q) {
        //     //         $q->select('id', 'name');
        //     //     },
        //     //     'category' => function ($q) {
        //     //         $q->select('id', 'name');
        //     //     },
        //     //     'Company' => function ($q) {
        //     //         $q->select('id', 'name');
        //     //     },
        //     //     'contactPerson' => function ($q) {
        //     //         $q->select('id', 'name');
        //     //     },
        //     //     'users' => function ($q) {
        //     //         $q->select('users.id', 'name');
        //     //     },
        //     //     'audits',
        //     //     // 'priorities' => function($q){
        //     //     //     $q->select('id', 'icons');
        //     //     // },

        //     // ])->latest()->get();
        // }
       
        // $this->response["status"] = true;
        // $this->response["message"] = __('strings.get_all_success');
        // $this->response["data"] = $tasks ?? [];
        // return response()->json($this->response);
    }
    public function inline_update(Request $request)
    {
        $valueOfassignedUser = 0;
        
        // return $original_date;
        if($request->route == 'leads'){
            $route = 'lead';
        }else{
            $route = 'task';

        }
        if($request->data){

            $priority_id = $request->data['id'];
            $task_id = $request->data['task_id'];
            
            $updateTask = Task::where(['type' =>  $route, 'id' => $task_id])->update([
                'priority' => $priority_id
            ]);
        }
        if($request->date){
            $task_id = $request->date['task_id'];
            if($request->date['due_date'] == 'today'){
                $datetime = new DateTime('today');

                $due_date = $datetime->format('Y-m-d');

            }else if($request->date['due_date'] == 'tomorrow'){
                $datetime = new DateTime('tomorrow');

                $due_date = $datetime->format('Y-m-d');
            }else{
                $due_date = $request->date['due_date'];
            }
            $updateTask = Task::where(['type' =>  $route, 'id' => $task_id])->update([
                'due_date' => $due_date
            ]);
        }
        if($request->user_data){
            $task_id = $request->user_data['task_id'];
            $user_id = $request->user_data['user_id'];
            $check_exists = TaskUser::where(['user_id' =>  $user_id, 'task_id' => $task_id])->first() ?? null;
            if($check_exists){
                $valueOfassignedUser = 1;
                TaskUser::where(['user_id' => $user_id, 'task_id' => $task_id])->forceDelete();

            }
            if(!$check_exists){

                TaskUser::create([
                    'user_id' => $user_id,
                    'task_id' => $task_id,
                    
                ]);
            }
         
            // return $request->user_data;
        }
        $get = Task::where('type' , $route)->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status_master_id', 'created_at')->with([
            'branch' => function ($q) {
                $q->where('name', 'banglo')->select('id', 'name');

            },
            'category' => function ($q) {
                $q->select('id', 'name');
            },
            'Company' => function ($q) {
                $q->select('id', 'name');
            },
            'contactPerson' => function ($q) {
                $q->select('id', 'name');
            },
            'users' => function ($q){
                
                $q->select('users.id', 'name', 'avatar');
            },
            'comments' => function($q){
                $q->select('id', 'comment', 'task_id', 'user_id', 'created_at');
            },
            'status_master',
            'audits',
            // 'priorities' => function($q){
            //     $q->select('id', 'icons');
            // },

        ])->latest()
        //    ->orderBy('created_at', 'desc')
           ->get();
        //    return $tasks;
// return Task::where('contact_person_id', 'LIKE', '%'.$filters['contact'].'%')->first();
$this->response["status"] = true;
if($valueOfassignedUser == 1){

    $this->response["message"] = 'User Unassigned';
}
if($valueOfassignedUser == 0){
    $this->response["message"] = 'Leads Updated!';

}
$this->response["data"] = $get ?? [];
return response()->json($this->response);
        // return [$route, $priority_id];
    }
    // public function leads(Request $request)
    // {
    //     return "hh";
    // $dbname = $request->header('X-Tenant');
    //     $dbname = config('tenancy.database.prefix').strtolower($dbname);
    //     // return   $dbname;
    //     $this->switchingDB($dbname);
    //     $tasks = Task::where('type', 'lead')->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id','user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status','created_at')->with([
    //         'branch' => function($q){
    //             $q->select('id', 'name');
    //         },
    //         'category' => function($q){
    //             $q->select('id','name');
    //         },
    //         'Company' => function($q){
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
     *             @OA\Property(property="company_id", type="string", example="1", description=""),
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
        $user = $request->user();
       $user_id = $user->id;

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required',
            'category_id' => 'nullable',
            'company_id' => 'nullable',
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

        $task = new Task();
        $branch_id_new_one = $request->branch_id['b_id'] ?? null;
        if($branch_id_new_one){

            $task->branch_id = (int)$request->branch_id['b_id'] ?? null;
        }
        else{
            $task->branch_id = $request->branch_id['id'] ?? null;

        }
        // return $task;

        $task->category_id = $request->category_id['id'] ?? null;


        $task->company_id = $request->company_id['id'] ?? null;


        $task->contact_person_id = $request->contact_person_id['id'] ?? null;

        // $task->user_id = $request->users[$i]['id'];
        $task->type = $request->type ?? null;
        $task->subject = $request->subject ?? null;
        $task->description = $request->description ?? null;
        $task->due_date = $request->due_date ?? null;

        $task->priority = $request->priority['id'] ?? null;

        $task->user_id = $user_id ?? '';
        // echo '<pre>';print_r($task);exit;
        $task->status_master_id = $request->status['id'] ?? 1;
        // return $request->users[$i];
        $task->save();
      $task_det = $task; 
        if($request->users && count($request->users) > 0){

        
        for ($i = 0; $i < count($request->users); $i++) {

            $taskss = Task::find($task->id);

            $taskUser = $taskss->users()->find($request->users[$i]['id']);
            // if($taskUser){
            //     $this->response["message"] = __('strings.store_failed');
            //     return response()->json($this->response);
            // }

            $taskss->users()->attach($request->users[$i]['id']);
        }
    }

        $data = [
            'type' => 'dont_delete',
        ];
        $branch = Branch::where(['id' => $request->branch_id])->update($data);
        $Category = Category::where(['id' => $request->category_id])->update($data);
        $Company = Company::where(['id' => $request->company_id])->update($data);
        $ContactPerson = ContactPerson::where(['id' => $request->contact_person_id])->update($data);
        if($request->mailbox_id && $task_det){
            $lead_task_id = $task_det->type.'_'.$task_det->id;
            Mailbox::where('id',$request->mailbox_id)->update(['task_lead_id' => $lead_task_id]);
        }

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
        // return $id;
        $validator = Validator::make(['task_id' => $id], [
            'task_id' => 'required|exists:App\Models\Task,id',
        ]);
        if ($validator->fails()) {
            $this->response["code"] = "INVALID";
            $this->response["message"] = $validator->errors()->first();
            $this->response["errors"] = $validator->errors();
            return response()->json($this->response, 422);
        }

        $task = Task::select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status_master_id', 'created_at')->with([
            'branch' => function ($q) {
                $q->select('id', 'name');
            },
            'category' => function ($q) {
                $q->select('id', 'name');
            },
            'Company' => function ($q) {
                $q->select('id', 'name');
            },
            'contactPerson' => function ($q) {
                $q->select('id', 'name');
            },
            'users' => function ($q) {
                $q->select('users.id', 'name','avatar');
            },
            'comments' => function($q){
                $q->select('id', 'comment', 'task_id', 'user_id');
            },
            'status_master',
            'audits',
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
     *             @OA\Property(property="company_id", type="string", example="1", description=""),
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
        // return $request->all();
        $validator = Validator::make(['task_id' => $id] + $request->all(), [
            'task_id' => 'required|exists:App\Models\Task,id',
            'branch_id' => 'required',
            'category_id' => 'nullable',
            'company_id' => 'nullable',
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

        // return $request->all();
        
        $updateTask = Task::find($id);
        $updateTask->update([
            'subject' => $request->subject, 
            'description' => $request->description,
            'branch_id' => $request->branch_id['id'] ?? null,
            'company_id' => $request->company_id['id'] ?? null,
            'category_id' => $request->category_id['id'] ?? null,
            'contact_person_id' => $request->contact_person_id['id'] ?? null,
            'type' => $request->type,
            'due_date' => $request->due_date ?? null,
            'priority' => $request->priority['id'] ?? null,
            'status_master_id' => $request->status['id'] ?? null,
        ]);

        $real_task = Task::find($id);



        // UPDATE FOREIGN KEY TABLES

        if($request->branch_id){

            $branch = Branch::where(['id' => $request->branch_id['id']])->update(['type' => 'dont_delete']);
        }
        if($request->category_id){

            $Category = Category::where(['id' => $request->category_id['id']])->update(['type' => 'dont_delete']);
        }
        if($request->company_id){
            
            $Company = Company::where(['id' => $request->company_id['id']])->update(['type' => 'dont_delete']);
        }
        if($request->contact_person_id){
            
            $ContactPerson = ContactPerson::where(['id' => $request->contact_person_id['id']])->update(['type' => 'dont_delete']);
        }


        // $checkNewUser = array();
        // for ($i=0; $i < count($request->users); $i++) {
        $checkNewUser = TaskUser::where(['task_id' => $id])->get();
        // }
        $user_values = [];
        foreach ($checkNewUser as $newUser) {

            $user_values[] =  $newUser->user_id;
        }
        // insert newly added data
        // return "insert";
        foreach ($request->users as $input_users) {
            // return "forloop";
            if (!in_array($input_users['id'], $user_values)) {
                $new_taskUser = new TaskUser();
                $new_taskUser->task_id = $id;
                $new_taskUser->user_id = $input_users['id'];
                $new_taskUser->save();
            }
        }

        // delete added user values
        // return "h";
        foreach ($user_values as $user_row) {
            $actual_array = [];
            foreach ($request->users as $use) {
                $actual_array[] = $use['id'];
            }

            // return $user_row;
            if (!in_array($user_row, $actual_array)) {
                // return "user not present";
                // TaskUser::where(['user_id' => $user_row])->delete();
                TaskUser::where(['user_id' => $user_row])->forceDelete();
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

        // $task->fill($request->only(['task_id', 'branch_id', 'category_id', 'company_id', 'contact_person_id','user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status']));
        // $task->update();

        $this->response["status"] = true;
        $this->response["message"] = __('strings.update_success');
        $this->response['data'] = $real_task ?? [];
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
        if (!$task) {
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
