<?php

namespace App\Containers\Auth\Messages;

trait Messages
{
    public function messages()
    {
        return [
            'LOGIN_SUCCESS' => 'User was successfully logged in',
            'LOGIN_FAILED' => 'Unable to login user',
            'LOGOUT_SUCCESS' => 'User was successfully logged out',
            'LOGOUT_FAILED' => 'Unable to logout user',
            'FORGOT_EMAIL_SUCCESS' => 'Forgot password email sent successfully',
            'FORGOT_EMAIL_FAIL' => 'Forgot password email send fail',
            'RESET_PASSWORD_SUCCESS' => 'Reset password was successful. Re-login to continue..',
            'RESET_PASSWORD_FAIL' => 'Reset password failed',
        ];
    }
}