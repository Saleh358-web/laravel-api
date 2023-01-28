<?php

namespace App\Containers\Auth\Helpers;

use App\Containers\Auth\Helpers\UserTokenHelper;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class UserAuthHelper
{
    /**
     * Login a user using credentials provided
     * credentials are email an password
     * 
     * @param $creds ['email' => 'email@example.com', 'password' => 'password']
     * @return [ 'user' => auth()->user(), 'token' => $token ] | null
     */
    public static function login($creds)
    {
        if (auth()->attempt($creds)) {
            Log::info('Login successful');
            $token = UserTokenHelper::create_token(auth()->user());
            return [
                'user' => auth()->user(),
                'token' => $token
            ] ;
        }
        
        Log::info('Login failed');
        return null;
    }

    /**
     * Logout authenticated user
     * 
     * @return bool
     */
    public static function logout($user)
    {
        return UserTokenHelper::revoke_token_for_user(auth()->user()->token(), $user);
    }

    public static function logoutFromAllAndRefreshToken(User $user = null)
    {
        if($user == null) {
            $user = auth()->user();
        }

        UserTokenHelper::revoke_all($user);
        $token = UserTokenHelper::create_token($user);
        return $token;
    }
}