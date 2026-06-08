<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillsController;
use App\Http\Controllers\Api\DataShareController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\TransferController;
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
        Route::post('/virtual-card/withdraw', [AccountController::class, 'withdrawVirtualCard']);
        Route::post('/func-wallet', [AccountController::class, 'fundWallet']);
        Route::post('/id-verification', [AccountController::class, 'idVerification']);
        Route::post('/update-profile', [AccountController::class, 'updateProfile']);
        Route::post('/upgrade-to-agent', [AccountController::class, 'upgradeAgent']);
        Route::post('/cancel-agent-subscription', [AccountController::class, 'cancelAgentSubscription']);
        Route::post('/add-sub-agent', [AccountController::class, 'addSubAgent']);
        Route::post('/mono/connect', [AccountController::class, 'connectMonoAccount']);
        Route::post('/mono/create-mandate', [AccountController::class, 'createMonoMandate']);
        Route::post('/repay-owe-spl', [AccountController::class, 'repayOweSpl']);
    });

    Route::prefix('/main')->group(function () {
        Route::get('/list-all-banks', [TransferController::class, 'listAllBanks']);
        Route::get('/recent-airtime-topup', [BillsController::class, 'getRecentAirtimeTopup']);
        Route::get('/transactions', [TransactionController::class, 'listTransactions']);
        Route::get('/fetch-electric-service-codes', [BillsController::class, 'fetchElectricServiceCode']);
        Route::get('/verify-added-card', [TransactionController::class, 'verifyAddedCard']);
        Route::get('/list-debit-cards', [TransactionController::class, 'listDebitCards']);
        Route::get('/fetch-unpaid-data-loan', [BillsController::class, 'fetchUnpaidDataLoan']);
        Route::get('/fetch-single-data-loan/{dataLoan}', [BillsController::class, 'fetchSingleDataLoan']);
        Route::get('/recent-activity', [TransactionController::class, 'recentActivity']);
        Route::get('/fetch-data-loan-list', [BillsController::class, 'fetchDataLoanList']);
        Route::get('/fetch-data-loan-length', [BillsController::class, 'fetchDataLoanLength']);
        Route::get('/all-data-loan', [BillsController::class, 'allDataLoan']);
        Route::get('/data-banks', [DataShareController::class, 'dataBanks']);
        Route::get('/sent-data-banks', [DataShareController::class, 'sentDataBanks']);
        Route::get('/receive-data-banks', [DataShareController::class, 'receivedDataBanks']);
        Route::get('/my-data-banks/{dataBank}', [DataShareController::class, 'myDataBanks'])->whereNumber('dataBank');
        Route::get('/load-betting-companies', [BillsController::class, 'loadBettingCompanies']);
        Route::get('/fetch-cash-loan-length', [BillsController::class, 'fetchCashLoanLength']);
        Route::get('/fetch-unpaid-cash-loan', [BillsController::class, 'fetchUnpaidCashLoan']);
        Route::get('/all-cash-loan', [BillsController::class, 'allCashLoan']);
        Route::get('/list-janb-service', [BillsController::class, 'listJambService']);

        Route::post('/purchase-airtime', [BillsController::class, 'purchaseAirtime']);
    });
});
