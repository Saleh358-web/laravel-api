<?php

namespace App\Containers\Users\Permissions;

class Permissions
{
    public static function permissions()
    {
        return [
            [
                'name' => 'Get Users',
                'slug' => 'get-users',
                'description' => 'Get all users'
            ],
            [
                'name' => 'Create Users',
                'slug' => 'create-users',
                'description' => 'Create a new user with full data'
            ],
            [
                'name' => 'Attach Permissions',
                'slug' => 'attach-permissions',
                'description' => 'Add and remove permissions for user'
            ],
            [
                'name' => 'Activate/Deactivate User',
                'slug' => 'activate-user',
                'description' => 'Activate or deactivate a user\'s account'
            ],
            [
                'name' => 'Delete User',
                'slug' => 'delete-user',
                'description' => 'Delete a user\'s account'
            ],
            [
                'name' => 'Get deleted users',
                'slug' => 'get-deleted-users',
                'description' => 'Get the list of deleted users'
            ],
            [
                'name' => 'Get inactive users',
                'slug' => 'get-inactive-users',
                'description' => 'Get the list of inactive users'
            ]
        ];
    }
}