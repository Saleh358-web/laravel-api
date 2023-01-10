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
            'email_exists' => 'Email already exists'
        ];
    }
}
