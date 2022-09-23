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
use App\Http\Controllers\Api\Tenant\ClientController;
use App\Http\Controllers\Api\Tenant\ContactPersonController;
use App\Http\Controllers\Api\Tenant\ContactPersonEmailController;
use App\Http\Controllers\Api\Tenant\ContactPersonPhoneController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\Tenant\MailConfigController;
use App\Http\Controllers\Api\Tenant\EmailOutboundController;
use App\Http\Controllers\Api\Tenant\EmailInboundController;
use App\Http\Controllers\Api\Tenant\EmailMasterController;
use App\Http\Controllers\Api\Tenant\MailboxController;

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
            Route::apiResource('tasks', TaskController::class);
            Route::get('all-users', [ApiUserController::class, 'fetch']);

            Route::post('users/{id}/deactivate', [UserController::class, 'deactivate']);
            Route::get('/users/get-emails-to-assign', [UserController::class, 'get_emails_to_assign']);
            Route::apiResource('users', UserController::class);

            Route::apiResource('tasks.users', TaskUserController::class);
            Route::apiResource('tasks.comments', TaskCommentController::class);
            Route::apiResource('to-dos', ToDoController::class);
            Route::apiResource('clients', ClientController::class);
            Route::get('contact-people/leads',[ContactPersonController::class,'getDataForLeads']);
            Route::apiResource('contact-people', ContactPersonController::class);
            Route::apiResource('contact-people.emails', ContactPersonEmailController::class);
            Route::apiResource('contact-people.phones', ContactPersonPhoneController::class);
            Route::post('set-password', [ResetPasswordController::class, 'setPassword']);
            // Route::get('recieve-emails', [UserController::class, 'emails_recieved']);
            //------------------------EmailConfig -----------------------------
            Route::post('/store-email',[EmailMasterController::class,'storeMail']);
            Route::post('/get-emails',[EmailMasterController::class,'getEmailCredential']);
            Route::post('/find-emails',[EmailMasterController::class,'show']);
            Route::match(['put', 'patch'],'update-email/{id}', [EmailMasterController::class, 'update']);
            Route::post('/apps/email/emails', [MailboxController::class, 'fetchEmails']);
            Route::post('/apps/email/update-emails', [MailboxController::class, 'updateEmails']);

            Route::apiResource('email-outbound', EmailOutboundController::class);
            Route::apiResource('email-inbound', EmailInboundController::class);
            Route::post('email-outbound-status', [EmailOutboundController::class, 'update_active_inactive_status']);
            Route::post('email-inbound-status', [EmailInboundController::class, 'update_active_inactive_status']);
           
            

         
            Route::post('/tasks/filter-data', [TaskController::class, 'filterData']);

            
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
