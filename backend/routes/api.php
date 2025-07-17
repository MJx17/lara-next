<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrivilegeAccessController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', fn(Request $request) => $request->user());

    // Standard CRUD
    Route::get('/privilege-requests', [PrivilegeAccessController::class, 'index']);
    Route::post('/privilege-requests', [PrivilegeAccessController::class, 'store']);
    // routes/api.php or auth.php if you're using Laravel Breeze API routes
    Route::post('/privilege-requests/{uuid}/approve', [PrivilegeAccessController::class, 'approveByUuid']);
    Route::post('/privilege-requests/{uuid}/decline', [PrivilegeAccessController::class, 'declineByUuid']);
    Route::get('/privilege-requests/latest', [PrivilegeAccessController::class, 'latestForUser']);

    // UUID-based polling (for frontend or links)
    Route::get('/access-request/status/{uuid}', [PrivilegeAccessController::class, 'getStatusByUuid']);
});

