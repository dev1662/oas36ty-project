<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\InitializeTenancyByRequestData;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

use Illuminate\Http\Request;

use App\Http\Controllers\Api\Tenant\SwitchOrganizationController;
use App\Http\Controllers\Api\Tenant\TaskController;
use App\Http\Controllers\Api\Tenant\UserController;
use App\Http\Controllers\Api\Tenant\TaskUserController;
use App\Http\Controllers\Api\Tenant\TaskCommentController;
use App\Http\Controllers\Api\Tenant\ToDoController;
use App\Http\Controllers\Api\Tenant\ClientController;
use App\Http\Controllers\Api\Tenant\ClientContactPersonController;
use App\Http\Controllers\Api\Tenant\ClientContactPersonEmailController;
use App\Http\Controllers\Api\Tenant\ClientContactPersonPhoneController;

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
            Route::apiResource('tasks', TaskController::class);
            
            Route::post('users/{id}/deactivate', [UserController::class, 'deactivate']);
            Route::apiResource('users', UserController::class);

            Route::apiResource('tasks.users', TaskUserController::class);
            Route::apiResource('tasks.comments', TaskCommentController::class);
            Route::apiResource('to-dos', ToDoController::class);
            Route::apiResource('clients', ClientController::class);
            Route::apiResource('clients.contact-people', ClientContactPersonController::class);
            Route::apiResource('clients.contact-people.emails', ClientContactPersonEmailController::class);
            Route::apiResource('clients.contact-people.phones', ClientContactPersonPhoneController::class);
        });
    });
});

InitializeTenancyByRequestData::$onFail = function ($exception, $request, $next) {
    return response()->json(['message' => 'Invalid Client!'], 400);
};