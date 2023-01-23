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
     * @return Role $smallestRole | InvalidArgumentException
     */
    public static function getHighestRole($roles)
    {
        if(count($roles) == 0) {
            // roles should be an array of Role model
            throw new Exception\InvalidArgumentException('Roles should be an array of Role model');
        }

        // In Case of roles the smallest role id is the highest role
        $smallestRole = $roles[0];

        foreach($roles as $role) {
            $role->id < $smallestRole->id ? $smallestRole = $role : $smallestRole = $smallestRole;
        }

        return $smallestRole;
    }
}