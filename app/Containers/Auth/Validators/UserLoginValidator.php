<?php

namespace App\Containers\Auth\Validators;

use Illuminate\Support\Facades\Validator;

trait UserLoginValidator
{
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

    /**
     * Get a validator for an forgot password request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function forgot_password_validator(array $data)
    {
        $rules = [
            'email' => 'required|email',
        ];

        return Validator::make($data, $rules);
    }
}