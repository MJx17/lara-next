<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrivilegeAccessController;
use App\Http\Controllers\PrivilegeAccessTeamsController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());

    // Only fetch active (pending + not expired) requests
    Route::get('/privilege-requests/active', [PrivilegeAccessController::class, 'active']);
    Route::get('/privilege-requests', [PrivilegeAccessController::class, 'index']);
    Route::post('/privilege-requests', [PrivilegeAccessController::class, 'store']);
    Route::post('/privilege-requests/{uuid}/approve', [PrivilegeAccessController::class, 'approveByUuid']);
    Route::post('/privilege-requests/{uuid}/decline', [PrivilegeAccessController::class, 'declineByUuid']);
    Route::get('/privilege-requests/latest', [PrivilegeAccessController::class, 'latestForUser']);

    // UUID-based polling (for frontend or links)
    Route::get('/access-request/status/{uuid}', [PrivilegeAccessController::class, 'getStatusByUuid']);

    // New Teams notification endpoint

    Route::post('/privilege-requests/{uuid}/notify-teams', [PrivilegeAccessTeamsController::class, 'sendToTeams']);
});
