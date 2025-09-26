<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

// Broadcast::routes(['middleware' => ['auth:sanctum']]);
Broadcast::routes(['middleware' => ['auth:api']]);

Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');

Route::post('/login', [AuthController::class, 'login']);
