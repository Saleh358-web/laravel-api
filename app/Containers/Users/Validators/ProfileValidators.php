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

    /**
     * Get a validator for an incoming update user's password request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function update_password_validator(array $data)
    {
        $rules = [
            'old_password' => 'required|string|min:2',
            'password' => [
                'required',
                'min:6',
                'regex:/^.*(?=.{3,})(?=.*[a-zA-Z])(?=.*[0-9])(?=.*[\d\x])(?=.*[!$#@-_%]).*$/',
                'confirmed'
            ]
        ];

        return Validator::make($data, $rules);
    }

    /**
     * Get a validator for an incoming update user's profile photo request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function update_profile_photo_validator(array $data)
    {
        $rules = [
            'photo' => 'nullable|mimes:jpeg,png,jpg',
        ];

        return Validator::make($data, $rules);
    }
}