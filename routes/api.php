<?php

use App\Http\Controllers\Api\AccountController;
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

    // Account Controller
    Route::prefix('/account')->group(function () {
        Route::get('/wallets', [AccountController::class, 'wallets']);
        Route::get('/virtual-card', [AccountController::class, 'virtualCard']);
        Route::get('/virtual-card/{card_id}', [AccountController::class, 'virtualCardDetail']);
        Route::get('/virtual-card/{card_id}/transactions', [AccountController::class, 'virtualCardTransactions']);
        Route::get('/owe-sell-pay-later', [AccountController::class, 'oweSellPayLater']);
        Route::get('/owe-spl-history', [AccountController::class, 'oweSplHistory']);

        Route::post('/validate-bvn', [AccountController::class, 'validateBvn']);
        Route::post('/change-account-password', [AccountController::class, 'changeAccountPassword']);
        Route::post('/request-transfer-pin-reset-token', [AccountController::class, 'requestTransferPinResetToken']);
        Route::post('/change-transfer-pin', [AccountController::class, 'changeTransferPin']);
        Route::post('/request-lockscreen-pin-reset-token', [AccountController::class, 'requestLockscreenPinResetToken']);
        Route::post('/change-lockscreen-pin', [AccountController::class, 'changeLockscreenPin']);
        Route::post('/delete-account', [AccountController::class, 'deleteAccount']);
        Route::post('/create-virtual-card', [AccountController::class, 'createVirtualCard']);
        Route::post('/virtual-card/block/{card_id}', [AccountController::class, 'blockVirtualCard']);
        Route::post('/virtual-card/unblock/{card_id}', [AccountController::class, 'unblockVirtualCard']);
        Route::post('/virtual-card/fund', [AccountController::class, 'fundVirtualCard']);
    });
});
