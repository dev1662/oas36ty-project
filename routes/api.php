<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\SignUpController;
use App\Http\Controllers\Api\LoginController;
// use App\Http\Controllers\Api\ChooseOrganizationController;

use App\Http\Controllers\Api\Tenant\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('signup')->group(function(){
    Route::post('send-email', [SignUpController::class, 'sendEmail']);
    Route::post('verify-email', [SignUpController::class, 'verifyEmail']);
    Route::post('organization', [SignUpController::class, 'organization']);
    Route::post('complete', [SignUpController::class, 'complete']);
});

// Route::post('choose-organization', [ChooseOrganizationController::class, 'index']);
Route::post('login', [LoginController::class, 'index']);
// Route::get('profile', [ProfileController::class, 'index']);