<?php

namespace App\Helpers\User;

use Illuminate\Support\Facades\Validator;

trait UserValidator
{
    /*
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function create_validator(array $data)
    {
        $rules = [
            'first_name' => 'required|min:3',
            'last_name' => 'required|min:3',
            'email' => 'required|string|min:4|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ];

        return Validator::make($data, $rules);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function login_validator(array $data)
    {
        $rules = [
            'email' => 'required|email',
            'password' => 'required|min:8',
        ];

        return Validator::make($data, $rules);
    }
}