<?php

namespace App\Containers\Auth\Helpers;

use App\Containers\Auth\Helpers\UserTokenHelper;

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