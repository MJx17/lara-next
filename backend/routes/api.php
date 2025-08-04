<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrivilegeAccessController;



Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());

    Route::prefix('/privilege-requests')->group(function () {
        Route::get('/active', [PrivilegeAccessController::class, 'active']);
        Route::get('/', [PrivilegeAccessController::class, 'index']);
        Route::post('/', [PrivilegeAccessController::class, 'store']);
        Route::post('{uuid}/approve', [PrivilegeAccessController::class, 'approveByUuid']);
        Route::post('{uuid}/decline', [PrivilegeAccessController::class, 'declineByUuid']);
        Route::get('/status/{uuid}', [PrivilegeAccessController::class, 'getStatusByUuid']);
    });

    Route::get('/ping', fn() => response()->json(['message' => 'pong']));
});
