<?php

namespace App\Containers\Users\Helpers;

use App\Models\Role;

class UserRolesHelper
{
    /**
     * This function receives an array of Role models
     * and returns the role that has the smallest id
     * which is the role with the highest priority.
     * 
     * @param Role[] $roles
     * @return Role $smallestRole | null
     */
    public static function getHighestRole($roles)
    {
        if(count($roles) == 0) {
            // roles should be an array of Role model
            return null;
        }

        // In Case of roles the smallest role id is the highest role
        $highestRole = $roles[0];

        foreach($roles as $role) {
            if(!$role || !$highestRole || !$role->id || !$highestRole->id) {
                return null;
            }
            $role->id < $highestRole->id ? $highestRole = $role : $highestRole = $highestRole;
        }

        return $highestRole;
    }
}