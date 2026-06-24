<?php

use App\Http\Controllers\Webhooks\FidelityWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('/webhook')->group(function () {
    Route::post('/fidelity', [FidelityWebhookController::class, 'handle']);
});
