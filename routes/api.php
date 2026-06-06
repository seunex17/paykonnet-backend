<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forget-password', [AuthController::class, 'forgetPassword']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    // Auth Controller
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/resend-email-verify-code', [AuthController::class, 'resendEmailVerifyCode']);
    Route::post('/lockscreen', [AuthController::class, 'lockscreen']);
    Route::post('/verify-lockscreen', [AuthController::class, 'verifyLockscreen']);
    Route::post('/transfer-pin', [AuthController::class, 'transferPin']);
    Route::post('/logged-user', [AuthController::class, 'loggedUser']);
});
