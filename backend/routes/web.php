<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Middleware\VerifyCsrfToken;
use App\Http\Controllers\PrivilegeAccessController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});


Route::get('/privilege-requests/{uuid}/approve-teams', [PrivilegeAccessController::class, 'approveByUuidTeams'])
    ->name('privilege.approve-teams');

Route::get('/privilege-requests/{uuid}/decline-teams', [PrivilegeAccessController::class, 'declineByUuidTeams'])
    ->name('privilege.decline-teams');
