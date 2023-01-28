<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Containers\Auth\Controllers\PassportAuthController;

// Login
Route::post('v1/login', [PassportAuthController::class, 'login'])->name('login');

// Password forgot and reset
Route::post('v1/forgotPassword', [PassportAuthController::class, 'forgotPassword'])->name('password.forgot');
Route::post('v1/resetPassword', [PassportAuthController::class, 'resetPassword'])->name('password.reset');

Route::group([
    'prefix' => 'v1',
    'middleware' => ['auth:api']
], function ()
{
    // Logout
    Route::post('/logout', [PassportAuthController::class, 'logout'])->name('logout');
});