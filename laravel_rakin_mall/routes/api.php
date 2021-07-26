<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', [LoginController::class, 'logout']);
    Route::patch('settings/password', [PasswordController::class, 'update']);
    Route::post('password/change-password', [PasswordController::class, 'changePassword']);
    Route::post('password/lock', [VerificationController::class, 'lock']);

    // Route::get('user', [UserController::class, 'current']);
    // Route::patch('settings/profile', [ProfileController::class, 'update']);
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', [LoginController::class, 'authenticate']);
    Route::post('register', [RegisterController::class, 'register']);
    Route::post('password/reset', [ResetPasswordController::class, 'reset']);
    Route::post('password/validate-password-reset', [ResetPasswordController::class, 'validatePasswordReset']);
    Route::patch('password/token', [PasswordController::class, 'password']);
    Route::get('email/activate/{token}', [VerificationController::class, 'activate']);
    
    // Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail']);
    // Route::post('email/verify/{user}', [VerificationController::class, 'verify'])->name('verification.verify');
    // Route::post('email/resend', [VerificationController::class, 'resend']);
    // Route::post('oauth/{driver}', [OAuthController::class, 'redirect']);
    // Route::get('oauth/{driver}/callback', [OAuthController::class, 'handleCallback'])->name('oauth.callback');
});

Route::group(['middleware' => ['jwt.auth']], function () {
    // Upload module
    Route::post('media/upload', [UploadController::class, 'upload']);
    Route::get('media/fetch/{id}', [UploadController::class, 'fetch']);
    Route::delete('media/delete/{id}', [UploadController::class, 'delete']);
    // User module
    Route::get('user/pre-requisite', [UserController::class, 'preRequisite']);
    Route::get('user', [UserController::class, 'index']);
    Route::get('user/{id}', [UserController::class, 'show']);
    Route::post('user', [UserController::class, 'store']);
    Route::patch('user/{id}', [UserController::class, 'update']);
    Route::delete('user/{uuid}', [UserController::class, 'destroy']);
});