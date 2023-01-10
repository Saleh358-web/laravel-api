<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Containers\Users\Controllers\ProfileController;

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
});