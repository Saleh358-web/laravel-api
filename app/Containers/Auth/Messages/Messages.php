<?php

namespace App\Containers\Auth\Messages;

trait Messages
{
    public function messages()
    {
        return [
            'login_success' => 'User was successfully logged in',
            'login_failed' => 'Unable to login user',
            'logout_success' => 'User was successfully logged out',
            'logout_failed' => 'Unable to logout user',
            'forgot_email_sent' => 'Forgot password email sent successfully',
            'forgot_email_fail' => 'Forgot password email send fail'
        ];
    }
}