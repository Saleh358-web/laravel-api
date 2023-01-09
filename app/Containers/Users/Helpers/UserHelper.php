<?php

namespace App\Containers\Users\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Containers\Users\Exceptions\UpdateUserFailedException;
use App\Containers\Users\Exceptions\DuplicateEmailException;
use App\Containers\Users\Exceptions\CreateUserFailedException;
use Exception;

class UserHelper
{
    /**
     * create user
     * 
     * @param  array $data
     * @return User | CreateUserFailedException | Exception
     */
    public static function create(array $data)
    {
        DB::beginTransaction();
        try {
            $data = UserHelper::trimUserData($data);

            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'])
            ]);

            DB::commit();
            
            return $user;
        } catch (\Exception $e) {
            DB::rollback();
            throw new CreateUserFailedException();
        }

        DB::rollback();
        throw new CreateUserFailedException();
    }
    
     /**
     * update user
     * 
     * @param  User $user
     * @param  array $data
     * @return User | DuplicateEmailException | UpdateUserFailedException
     */
    public static function update(User $user, array $data)
    {

        DB::beginTransaction();
        try {
            $data = UserHelper::trimUserData($data);

            $user->first_name = $data['first_name'];
            $user->last_name = $data['last_name'];

            if($user->email != $data['email']) {
                $emailCount = User::where('email',  $data['email'])->count();
                if($emailCount) {
                    // New Email exists
                    throw new DuplicateEmailException();
                }

                $user->email = $data['email'];
            }

            $user->save();

            DB::commit();
            
            $user = User::find($user->id);
            return $user;
        } catch (\Exception $e) {
            DB::rollback();

            if($e->getMessage() != null) {
                // We have a normal exception
                throw new UpdateUserFailedException();
            }
            throw $e;
        }

        DB::rollback();
        throw new UpdateUserFailedException();
    }

    public static function trimUserData(array $data)
    {
        if(isset($data['first_name']) && $data['first_name'] != '') {
            $data['first_name'] = trim($data['first_name']);
        }
        if(isset($data['last_name']) && $data['last_name'] != '') {
            $data['last_name'] = trim($data['last_name']);
        }
        if(isset($data['email']) && $data['email'] != '') {
            $data['email'] = trim($data['email']);
        }
        if(isset($data['password']) && $data['password'] != '') {
            $data['password'] = trim($data['password']);
        }
        return $data;
    }
}