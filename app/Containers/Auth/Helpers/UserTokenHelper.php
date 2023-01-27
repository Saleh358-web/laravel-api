<?php

namespace App\Containers\Auth\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserTokenHelper
{
    public static function create_token(User $user)
    {
        $token = $user->createToken(UserTokenHelper::get_hashing_value())->accessToken;

        return $token;
    }

    public static function revoke_token_for_user($token, $user)
    {
        $token = $user->tokens->find($token);
        
        $token->revoke();

        Log::info('Token revoked for user');

        return true;
    }

    private static function get_hashing_value()
    {
        return env('APP_NAME') . now();
    }
}