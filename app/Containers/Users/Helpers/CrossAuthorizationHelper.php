<?php

namespace App\Containers\Users\Helpers;

use App\Containers\Users\Helpers\UserRolesHelper;
use App\Containers\Users\Helpers\UserHelper;

use App\Exceptions\Common\NotFoundException;

use App\Models\User;

class CrossAuthorizationHelper
{
    /**
     * This function checks if the user is allowed to
     * edit base information for the users of the array of ids
     * 
     * For example an admin is not allowed to edit another admin's info
     * but he can edit his own information and other normal users
     * 
     * And a super admin can't edit a fellow super admin's data
     * 
     * This function checks such information on a role level only!!
     * 
     * Base information are in this case:
     * first_name, last_name, email, password, active, profile_photo
     * and roles and permissions
     * 
     * @param User $user
     * @param $userIds
     * @return boolean | NotFoundException
     */
    public static function crossAuthorized(User $user, $userIds)
    {
        if(!$user || isset($user->deleted_at) || !$user->active) {
            throw new NotFoundException('User');
        }

        $currentUserHighestRole = UserRolesHelper::getHighestRole($user->roles()->get());

        if(!$currentUserHighestRole) {
            return false;
        }

        foreach($userIds as $userId) {
            $crossedUser = UserHelper::id($userId);
            if(!$crossedUser) {
                throw new NotFoundException('User');
            }

            $crossedUserRoles = $crossedUser->roles()->get();

            $crossedUserHighestRole = UserRolesHelper::getHighestRole($crossedUserRoles);

            if($crossedUserHighestRole && $crossedUserHighestRole->id <= $currentUserHighestRole->id) {
                // this user is not allowed to edit this user'
                return false;
            }
        }

        return true;
    }
}