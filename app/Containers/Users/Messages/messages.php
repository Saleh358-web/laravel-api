<?php

namespace App\Containers\Users\Messages;

trait Messages
{
    public function messages()
    {
        return [
            'profile' => [
                'get' => 'Returned Profile',
                'error' => 'Unable to get user profile'
            ]
        ];
    }
}
