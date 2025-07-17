<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PrivilegeAccessController;


Route::get('/', function () {
    return ['Laravel' => app()->version()];
});



