<?php

namespace App\Containers\Users\Validators;

use Illuminate\Support\Facades\Validator;

trait ProfileValidators
{
    /*
     * Get a validator for an incoming creation request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function create_validator(array $data)
    {
        $rules = [
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'email' => 'required|string|min:4|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
        ];

        return Validator::make($data, $rules);
    }

    /**
     * Get a validator for an incoming update profile request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function update_validator(array $data)
    {
        $rules = [
            'first_name' => 'required|string|min:2',
            'last_name' => 'required|string|min:2',
            'email' => 'required|string|min:4|email',
        ];

        return Validator::make($data, $rules);
    }
}