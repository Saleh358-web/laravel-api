<?php

namespace App\Helpers\User;

use App\Models\User;

class UserTokenHelper
{
    public static function create_token(User $user)
    {
        $token = $user->createToken(UserTokenHelper::get_hashing_value())->accessToken;

        return $token;
    }

    private static function get_hashing_value()
    {
        return env('APP_NAME');
    }
}