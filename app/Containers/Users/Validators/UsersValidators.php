<?php

namespace App\Containers\Users\Validators;

use Illuminate\Support\Facades\Validator;

trait UsersValidators
{
    /*
     * Get a validator for an incoming
     * add permissions to user request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function add_permissions_to_user(array $data)
    {
        $rules = [
            'user_id' => 'required|exists:users,id',
            'permissions' => 'required',
            'permissions.*' => 'required|exists:permissions,id',
        ];

        return Validator::make($data, $rules);
    }

}