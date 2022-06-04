<?php

namespace App\Helpers\User;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserCreateHelper
{
    public static function create(array $data)
    {
        $data = UserCreateHelper::trimUserData($data);
        
        return User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }

    public static function trimUserData(array $data)
    {
        $data['first_name'] = trim($data['first_name']);
        $data['last_name'] = trim($data['last_name']);
        $data['email'] = trim($data['email']);
        $data['password'] = trim($data['password']);
        return $data;
    }
}