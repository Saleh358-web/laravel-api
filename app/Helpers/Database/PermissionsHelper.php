<?php

namespace App\Helpers\Database;

use Illuminate\Support\Facades\DB;
use App\Models\Permission;

trait PermissionsHelper
{
    /**
     * This function gets permission data that are
     * name and slug
     * It checks if permission exists
     * creates it if it doesn't
     * 
     * @param $permissionData
     * @return void
     */
    public function addPermission($permissionData): void
    {
        DB::beginTransaction();
        try {
            Permission::create($permissionData);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    /**
     * This function seeds into the database all the permissions
     * needed to run in the api
     * 
     * @return void
     */
    public function addPermissions(): void
    {
        $permissions = [
            [
                'name' => 'Get Users',
                'slug' => 'get-users',
                'description' => 'Get all users'
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

        foreach($permissions as $perm) {
            $this->addPermission($perm);
        }
    }
}