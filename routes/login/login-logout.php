<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\PassportAuthController;

Route::post('login', [PassportAuthController::class, 'login']);