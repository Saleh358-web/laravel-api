<?php

namespace App\Containers\Users\Validators;

use Illuminate\Support\Facades\Validator;

trait UsersValidators
{
    /*
     * Get a validator for an incoming
     * add/remove permissions to user request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function permissions_user(array $data)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'permissions' => 'required',
            'permissions.*' => 'required|exists:permissions,id',
        ];

        return Validator::make($data, $rules);
    }

    /*
     * Get a validator for an incoming
     * add/remove roles to user request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function roles_user(array $data)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'roles' => 'required',
            'roles.*' => 'required|exists:roles,id',
        ];

        return Validator::make($data, $rules);
    }
}