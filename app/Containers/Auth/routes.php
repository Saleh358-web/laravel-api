<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Containers\Auth\Controllers\PassportAuthController;

Route::post('v1/login', [PassportAuthController::class, 'login'])->name('login');

Route::group([
    'prefix' => 'v1',
    'middleware' => ['auth:api']
], function ()
{
    // Logout
    Route::post('/logout', [PassportAuthController::class, 'logout'])->name('logout');
});