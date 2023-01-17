<?php

namespace App\Containers\Users\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantsHelper;
use App\Containers\Users\Exceptions\UpdateUserFailedException;
use App\Containers\Users\Exceptions\DuplicateEmailException;
use App\Containers\Users\Exceptions\CreateUserFailedException;
use App\Containers\Users\Exceptions\OldPasswordException;
use App\Containers\Users\Exceptions\SameOldPasswordException;
use App\Containers\Users\Exceptions\UpdatePasswordFailedException;
use Exception;

class UserHelper
{
    /**
     * get all users
     * 
     * @return pagination of users
     */
    public static function getAll(int $paginationCount = null)
    {
        try {
            $paginationCount = ConstantsHelper::getPagination($paginationCount);

            $users = User::with(['roles', 'permissions'])->paginate($paginationCount);
            return $users;
        } catch (\Exception $e) {
            return [];
        }

        return [];
    }

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

    /**
     * update user password
     * 
     * @param  User $user
     * @param  array $data
     * @return User | OldPasswordException | SameOldPasswordException | UpdatePasswordFailedException
     */
    public static function updatePassword(User $user, array $data)
    {
        DB::beginTransaction();

        try {
            $data = UserHelper::trimPasswords($data);

            if(!Hash::check($data['old_password'], $user->password)) {
                throw new OldPasswordException();
            }

            if(Hash::check($data['password'], $user->password)) {
                throw new SameOldPasswordException();
            }

            $user->password = Hash::make($data['password']);
            $user->password_updated_at = now();
            $user->save();

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            if($e->getMessage() != null) {
                // We have a normal exception
                throw new UpdatePasswordFailedException();
            }
            throw $e;
        }

        DB::rollback();
        throw new UpdatePasswordFailedException();
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

    public static function trimPasswords(array $data)
    {
        if(isset($data['old_password']) && $data['old_password'] != '') {
            $data['old_password'] = trim($data['old_password']);
        }
        if(isset($data['password']) && $data['password'] != '') {
            $data['password'] = trim($data['password']);
        }
        return $data;
    }
}