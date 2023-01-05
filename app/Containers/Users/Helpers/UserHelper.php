<?php

namespace App\Containers\Users\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserHelper
{
    /**
     * create user
     * 
     * @param  array $data
     * @return StatusArray [ 'status' => boolean, '{any}' => {any} ]
     */
    public static function create(array $data)
    {
        $data = UserHelper::trimUserData($data);

        DB::beginTransaction();
        try {
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);

            DB::commit();
            
            return [
                'status' => true,
                'user' => $user
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'status' => false,
                'case' => 'create_failed'
            ];
        }

        DB::rollback();
        return [
            'status' => false,
            'case' => 'create_failed'
        ];
    }
    
     /**
     * update user
     * 
     * @param  User $user
     * @param  array $data
     * @return StatusArray [ 'status' => '', 'case' => '' ]
     */
    public static function update(User $user, array $data)
    {
        $data = UserHelper::trimUserData($data);

        DB::beginTransaction();
        try {
            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];

            if($user->email != $data['email']) {
                $emailCount = User::where('email',  $data['email'])->count();
                if($emailCount) {
                    // New Email exists
                    return [
                        'status' => false,
                        'case' => 'email_exists'
                    ];
                }

                $user->email = $data['email'];
            }

            $user->save();

            DB::commit();
            
            $user = User::find($user->id);
            return [
                'status' => true,
                'user' => $user
            ];
        } catch (\Exception $e) {
            DB::rollback();
            return [
                'status' => false,
                'case' => 'update_failed'
            ];
        }

        DB::rollback();
        return [
            'status' => false,
            'case' => 'update_failed'
        ];
    }

    public static function trimUserData(array $data)
    {
        $data['first_name'] = trim($data['first_name']);
        $data['last_name'] = trim($data['last_name']);
        $data['email'] = trim($data['email']);
        if(isset($data['password']) && $data['password'] != '') {
            $data['password'] = trim($data['password']);
        }
        return $data;
    }
}