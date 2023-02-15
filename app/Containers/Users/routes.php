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
    Route::delete('/profile', [ProfileController::class, 'delete'])->name('profile.delete');

    // Profile Photo
    Route::post('/profile_image', [ProfileController::class, 'updatePhoto'])->name('profile.photo.upload');

    // Password
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    
    Route::group([
        'prefix' => 'users',
        'middleware' => ['roles:super-admin/admin']
    ], function ()
    {
        // Users
        Route::get('/', [UsersController::class, 'get'])->name('users.get');
        Route::get('/{id}', [UsersController::class, 'id'])->name('users.get.id');
        Route::post('/', [UsersController::class, 'create'])->name('users.create');
        Route::put('/{id}', [UsersController::class, 'update'])->name('users.update.id');

        // Add / Remove permissions to user
        Route::put('addPermissionsToUser', [UsersController::class, 'addPermissionsToUser'])
        ->name('users.addPermissionsToUser');
        Route::put('removePermissionsToUser', [UsersController::class, 'removePermissionsToUser'])
        ->name('users.addPermissionsToUser');

        // Add / Remove roles to user
        Route::put('addRolesToUser', [UsersController::class, 'addRolesToUser'])
        ->name('users.addRolesToUser');
        Route::put('removeRolesToUser', [UsersController::class, 'removeRolesToUser'])
        ->name('users.addRolesToUser');

        // User activation and deactivation
        Route::get('inactiveUsers', [UsersController::class, 'getInActiveUsers'])->name('users.get.inactive');
        Route::put('deactivateUsers', [UsersController::class, 'deactivateUsers'])->name('users.deactivateUsers');
        Route::put('activateUsers', [UsersController::class, 'activateUsers'])->name('users.activateUsers');

        // Users delete
        Route::get('deletedUsers', [UsersController::class, 'getDeletedUsers'])->name('users.get.deleted');
        Route::delete('deleteUsers', [UsersController::class, 'deleteUsers'])->name('users.delete');
    });

});