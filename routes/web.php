<?php

use App\Http\Controllers\Webhooks\FidelityWebhookController;
use App\Http\Controllers\Webhooks\USSDWebhookController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/test', function () {
    $user = User::find(9);
    $user->creditAdd(2000);
});

Route::prefix('/webhook')->group(function () {
    Route::post('/fidelity', [FidelityWebhookController::class, 'handle']);
    Route::post('/ussd/mobile-data', [USSDWebhookController::class, 'handleMobileData']);
});
