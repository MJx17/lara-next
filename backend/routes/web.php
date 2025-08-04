<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Routing\Middleware\VerifyCsrfToken;
use App\Http\Controllers\PrivilegeAccessController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});



