<?php

use App\Http\Controllers\Webhooks\FidelityWebhookController;
use App\Http\Controllers\Webhooks\USSDWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/webhook')->group(function () {
    Route::post('/fidelity', [FidelityWebhookController::class, 'handle']);
    Route::post('/ussd/mobile-data', [USSDWebhookController::class, 'handleMobileData']);
});
