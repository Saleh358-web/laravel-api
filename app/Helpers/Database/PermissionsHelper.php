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
            ],
            [
                'name' => 'Attach Permissions',
                'slug' => 'attach-permissions',
            ],
            [
                'name' => 'Activate/Deactivate User',
                'slug' => 'activate-user',
            ],
            [
                'name' => 'Delete User',
                'slug' => 'delete-user',
            ]
        ];

        foreach($permissions as $perm) {
            $this->addPermission($perm);
        }
    }
}