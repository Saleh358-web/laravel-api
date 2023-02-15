<?php

namespace App\Containers\Users\Messages;

trait Messages
{
    public function messages()
    {
        return [
            'PROFILE' => [
                'GET' => 'Returned Profile',
                'GET_ERROR' => 'Unable to get user profile',
                'EXCEPTION' => 'User profile',
                'UPDATE_ERROR' => 'User profile update failed',
                'UPDATE_SUCCESS' => 'User profile updated successfully',
                'CREATE_ERROR' => 'Create user failed',
                'CREATE_SUCCESS' => 'User created successfully',
                'PASSWORD' => 'Password updated successfully',
                'PASSWORD_ERROR' => 'Password updated failed. The password should have at least 6 characters, 1 capital letter, 1 number and 1 special character',
                'OLD_PASSWORD_ERROR' => 'Old password is incorrect',
                'OLD_PASSWORD_ERROR_EQUAL_NEW' => 'Password shouldn\'t be the same as the old one',
                'DELETE_SUCCESS' => 'Profile deleted successfully. You can restore your account if logged again within 60 days from now',
                'DELETE_ERROR' => 'Profile delete failed'
            ],
            'USERS' => [
                'GET' => 'Users found',
                'GET_ERROR' => 'Unable to get users',
                'ATTACH_PERMISSIONS' => 'Permissions added successfully',
                'ATTACH_PERMISSIONS_FAILED' => 'Attach permissions failed',
                'ATTACH_PERMISSIONS_NOT_ALLOWED' => 'This user is not allowed to attach/detach permissions',
                'DETACH_PERMISSIONS' => 'Permissions removed successfully',
                'DETACH_PERMISSIONS_FAILED' => 'Detach permissions failed',
                'ATTACH_ROLES' => 'Roles added successfully',
                'ATTACH_ROLES_FAILED' => 'Roles attach failed',
                'DETACH_ROLES' => 'Roles removed successfully',
                'DETACH_ROLES_FAILED' => 'Detach roles failed',
                'ATTACH_ROLES_NOT_ALLOWED'  => 'This user is not allowed to attach/detach roles',
                'ACTIVATE_DEACTIVATE_USER_NOT_ALLOWED' => 'This user is not allowed to activate/deactivate users',
                'CROSS_AUTH_ERROR' => 'User can only edit a user with a lower role hierarchy',
                'DEACTIVATE' => 'Deactivation was successful',
                'DEACTIVATE_ERROR' => 'Deactivation failed',
                'ACTIVATE' => 'Activation was successful',
                'ACTIVATE_ERROR' => 'Activation failed',
                'DELETE_USER_NOT_ALLOWED' => 'This user is not allowed to delete users',
                'DELETE_SUCCESSFUL' => 'Delete was successful',
                'DELETE_ERROR' => 'Delete failed',
                'CREATE_USER_NOT_ALLOWED' => 'This user is not allowed to create users',
                'CREATE_USER_SUCCESS' => 'User created successfully',
                'CREATE_USER_FAILED' => 'User create failed'
            ],
            'EMAIL_EXISTS' => 'Email already exists'
        ];
    }
}
