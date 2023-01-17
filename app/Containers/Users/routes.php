<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Containers\Users\Controllers\ProfileController;
use App\Containers\Users\Controllers\UsersController;

Route::group([
    'prefix' => 'v1',
    'middleware' => ['auth:api']
], function ()
{
    // Profile
    Route::get('/profile', [ProfileController::class, 'get'])->name('profile.get');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Password
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    
    Route::group([
        'prefix' => 'v1',
        'middleware' => ['roles:super-admin/admin']
    ], function ()
    {
        // Users
        Route::get('users', [UsersController::class, 'get'])->name('users.get');
    });

});