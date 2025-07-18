<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicPrivilegeAccessController;
use Illuminate\Routing\Middleware\VerifyCsrfToken;
Route::get('/', function () {
    return ['Laravel' => app()->version()];
});


