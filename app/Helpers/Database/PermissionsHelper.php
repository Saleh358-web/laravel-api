<?php

namespace App\Helpers\Database;

use App\Containers\Users\Permissions\Permissions as UserPermissions;

use Illuminate\Support\Facades\DB;
use App\Models\Permission;

use Exception;

class PermissionsHelper
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
    public static function addPermission($permissionData): void
    {
        DB::beginTransaction();
        try {
            Permission::create($permissionData);
            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
        }
    }

    /**
     * This function seeds into the database all the permissions
     * needed to run in the api
     * 
     * @return void
     */
    public static function addPermissions(): void
    {
        $permissions = self::getPermissions();

        foreach($permissions as $perm) {
            self::addPermission($perm);
        }
    }

    /**
     * This function returns all this permissions in this api
     * 
     * @return array
     */
    private static function getPermissions()
    {
        $permissions = array();
        
        $userPermissions = UserPermissions::permissions();
        $permissions = array_merge($permissions, $userPermissions);

        return $permissions;
    }

    /**
     * This function checks if if the permission is added to the database
     * and adds it if not
     * 
     * @param string $slug
     * @return void
     */
    public static function addBySlug(string $slug): void
    {
        try {
            if($slug && $slug != '') {
                $slug = trim($slug);

                $permissions = self::getPermissions();
                $permissionData = [];
                foreach($permissions as $perm) {
                    if($perm['slug'] == $slug) {
                        $permissionData = $perm;
                        break;
                    }
                }
                if(Permission::where('slug', $permissionData['slug'])->count() == 0) {
                    self::addPermission($permissionData);
                }
            }
        } catch (Exception $e) {
            throw $e;
        }
    }
}