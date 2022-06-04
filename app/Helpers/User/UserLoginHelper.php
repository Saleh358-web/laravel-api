<?php

namespace App\Helpers\User;

use App\Helpers\User\UserTokenHelper;

class UserLoginHelper
{
    public static function login($creds)
    {
        if (auth()->attempt($creds)) {
            $token = UserTokenHelper::create_token(auth()->user());
            return [
                'user' => auth()->user(),
                'token' => $token
            ] ;
        }
        
        return null;
    }
}