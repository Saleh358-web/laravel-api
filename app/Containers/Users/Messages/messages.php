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
                'create' => 'User created successfully'
            ],
            'email_exists' => 'Email already exists'
        ];
    }
}
