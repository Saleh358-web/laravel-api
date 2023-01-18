<?php

namespace App\Containers\Users\Helpers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantsHelper;
use App\Exceptions\Common\NotFoundException;
use App\Exceptions\Common\CreateFailedException;
use App\Exceptions\Common\UpdateFailedException;
use App\Containers\Users\Exceptions\DuplicateEmailException;
use App\Containers\Users\Exceptions\OldPasswordException;
use App\Containers\Users\Exceptions\SameOldPasswordException;
use App\Containers\Users\Exceptions\UpdatePasswordFailedException;
use Exception;

class UserHelper
{
    /**
     * get user base info (only from users table)
     * 
     * @param int $id
     * @return User $user
     */
    public static function id(int $id)
    {
        try {
            $user = User::find($id);

            if(!$user) {
                throw new NotFoundException('User');
            }

            return $user;
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    /**
     * get all users
     * 
     * @param int $paginationCount
     * @return pagination of users
     */
    public static function getAll(int $paginationCount = null)
    {
        try {
            $paginationCount = ConstantsHelper::getPagination($paginationCount);

            $users = User::with(['roles', 'permissions'])->paginate($paginationCount);
            $users = json_decode(json_encode($users)); // This will change its type to StdClass

            return $users;
        } catch (\Exception $e) {
            throw $e;
        }

        return [];
    }

    /**
     * create user
     * 
     * @param  array $data
     * @return User | CreateFailedException | Exception
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
            throw new CreateFailedException('User');
        }

        DB::rollback();
        throw new CreateFailedException('User');
    }
    
    /**
     * update user
     * 
     * @param  User $user
     * @param  array $data
     * @return User | DuplicateEmailException | UpdateFailedException
     */
    public static function update(User $user, array $data)
    {

        DB::beginTransaction();
        try {
            throw new CreateFailedException('User');

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
                throw new UpdateFailedException('User');
            }
            throw $e;
        }

        DB::rollback();
        throw new UpdateFailedException('User');
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

    /**
     * update user password
     * 
     * @param  User $user
     * @param  int $permissionId
     * @return boolean | Exception
     */
    public static function attachPermission(User $user, int $permissionId)
    {
        DB::beginTransaction();

        try {
            $permission = Permission::find($permissionId);

            if(!$permission) {
                throw new NotFoundException('Permission');
            }

            $user->permissions()->attach($permission);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            if($e->getMessage() != null) {
                // We have a normal exception
                // ToDo throw Exception
            }
            throw $e;
        }

        DB::rollback();
        // ToDo throw Exception
        return false;
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