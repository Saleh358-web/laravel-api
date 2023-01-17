<?php

namespace App\Containers\Users\Messages;

trait Messages
{
    public function messages()
    {
        return [
            'profile' => [
                'get' => 'Returned Profile',
                'get_error' => 'Unable to get user profile',
                'update_error' => 'User profile update failed',
                'update' => 'User profile updated successfully',
                'create_error' => 'Create user failed',
                'create' => 'User created successfully',
                'password' => 'Password updated successfully',
                'password_error' => 'Password updated failed. The password should have at least 6 characters, 1 capital letter, 1 number and 1 special character',
                'old_password_error' => 'Old password is incorrect',
                'old_password_equal_new' => 'Password shouldn\'t be the same as the old one',
            ],
            'users' => [
                'get' => 'Users found',
                'get_error' => 'Unable to get users',
                'attach_permissions' => 'Permissions added successfully',
                'attach_permissions_failed' => 'Attach permissions failed',
                'attach_permissions_not_allowed' => 'This user is not allowed to attach/detach permissions',
            ],
            'email_exists' => 'Email already exists'
        ];
    }
}
