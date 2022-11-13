<?php

declare(strict_types=1);

use App\Http\Controllers\Api\ResetPasswordController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\Tenant\AccountController;
use App\Http\Controllers\Api\Tenant\SwitchOrganizationController;
use App\Http\Controllers\Api\Tenant\BranchController;
use App\Http\Controllers\Api\Tenant\CategoryController;
use App\Http\Controllers\Api\Tenant\TaskController;
use App\Http\Controllers\Api\Tenant\UserController;
use App\Http\Controllers\Api\Tenant\TaskUserController;
use App\Http\Controllers\Api\Tenant\TaskCommentController;
use App\Http\Controllers\Api\Tenant\ToDoController;
use App\Http\Controllers\Api\Tenant\CompanyController;
use App\Http\Controllers\Api\Tenant\ContactPersonController;
use App\Http\Controllers\Api\Tenant\ContactPersonEmailController;
use App\Http\Controllers\Api\Tenant\ContactPersonPhoneController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\Tenant\MailConfigController;
use App\Http\Controllers\Api\Tenant\EmailOutboundController;
use App\Http\Controllers\Api\Tenant\EmailInboundController;
use App\Http\Controllers\Api\Tenant\EmailMasterController;
use App\Http\Controllers\Api\Tenant\MailboxController;
use App\Http\Controllers\Api\Tenant\StatusMasterController;
use App\Models\Task;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'api',
    InitializeTenancyByRequestData::class,
    // PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::prefix('v1')->group(function(){

        Route::group(['middleware' => ['auth:api', 'verified']], function () {
            Route::post('switch', [SwitchOrganizationController::class, 'index']);
            Route::post('logout', [AccountController::class, 'logout']);
            Route::apiResource('branches', BranchController::class);
            Route::apiResource('categories', CategoryController::class);

            Route::get('dev', function(){
                return response()->json("h");
                
            });
            Route::apiResource('tasks', TaskController::class)->parameters([
                'tasks' => 'id'
            ]);
            // Route::post('')
            Route::post('/sendEmail-outBound', [MailboxController::class, 'sendEmail']);
            
            Route::get('all-users', [ApiUserController::class, 'fetch']);
            
            Route::post('users/{id}/deactivate', [UserController::class, 'deactivate']);
            Route::get('/users/get-emails-to-assign', [UserController::class, 'get_emails_to_assign']);
            Route::apiResource('users', UserController::class);
            Route::get('contact-people/show_all', [ContactPersonController::class, 'showAll']);

            Route::apiResource('tasks.users', TaskUserController::class);
            Route::apiResource('tasks.comments', TaskCommentController::class);
// Route::get('/tasks/1/comments/assigned_users',[TaskCommentController::class, 'usersAssigned']);

            Route::apiResource('to-dos', ToDoController::class);
            Route::apiResource('Companys', CompanyController::class);
            Route::apiResource('status_master', StatusMasterController::class);
            Route::get('contact-people/leads',[ContactPersonController::class,'getDataForLeads']);
            Route::apiResource('contact-people', ContactPersonController::class);
            Route::apiResource('contact-people.emails', ContactPersonEmailController::class);
            Route::apiResource('contact-people.phones', ContactPersonPhoneController::class);
            Route::post('set-password', [ResetPasswordController::class, 'setPassword']);
            // Route::get('recieve-emails', [UserController::class, 'emails_recieved']);
            //------------------------EmailConfig -----------------------------
            Route::post('/store-email',[EmailMasterController::class,'storeMail']);
            Route::delete('/delete/{email_master}', [EmailMasterController::class, 'destroy']);
            Route::post('/get-emails',[EmailMasterController::class,'getEmailCredential']);
            Route::post('/find-emails',[EmailMasterController::class,'show']);
            Route::match(['put', 'patch'],'update-email/{id}', [EmailMasterController::class, 'update']);
            Route::post('/apps/email/emails', [MailboxController::class, 'fetchEmails']);
            // Route::post('/apps/email/sent', [MailboxController::class, 'fetch_sent_emails']);
            
            Route::post('/apps/email/update-emails', [MailboxController::class, 'updateEmails']);

            Route::apiResource('email-outbound', EmailOutboundController::class);
            Route::apiResource('email-inbound', EmailInboundController::class);
            Route::post('email-outbound-status', [EmailOutboundController::class, 'update_active_inactive_status']);
            Route::post('email-inbound-status', [EmailInboundController::class, 'update_active_inactive_status']);
           
            Route::get('apps/todo/tasks', [ToDoController::class, 'index']);
            Route::get('audits', function(){
                if($_GET['route'] == 'leads-inner-folder'){
                    $route = 'lead';
                }
                // $audits = DB::table('audits')->join('users', 'users.id', '=', 'audits.user_id')->get();
                $audits = Task::where(['type' =>  $route, 'id' => $_GET['id']])->with([
                    'audits'
                ])->select('id', 'branch_id', 'category_id', 'company_id', 'contact_person_id', 'user_id', 'type', 'subject', 'description', 'due_date', 'priority', 'status_master_id', 'created_at')
                ->latest()->get();
                $this->response['status'] = true;
                $this->response['message'] = 'Audits Fetched';
                $this->response['data'] = $audits ?? [];
                return response()->json($this->response);
            });
         
            Route::post('/tasks/filter-data', [TaskController::class, 'filterData']);
            Route::post('/tasks/inlineUpdate', [TaskController::class, 'inline_update']);
            Route::get('assignedEmails-outBound', [EmailOutboundController::class, 'fetchEmails_outbound']);
        });
        
    });
});

Route::prefix('v1')->group(function(){
   
    Route::get('recieve-emails', [UserController::class, 'emails_recieved']);
    
    //new user
    Route::post('set-password', [ResetPasswordController::class, 'setPassword']);
    // existing user accept
    Route::post('accept-invite', [UserController::class, 'AcceptInvite']);
    // existing user decline
    Route::post('decline-invite', [UserController::class, 'declineInvite']);
});
InitializeTenancyByRequestData::$onFail = function ($exception, $request, $next) {
    return response()->json(['message' => 'Invalid Client!'], 400);
};
