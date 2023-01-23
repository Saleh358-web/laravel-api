<?php

namespace App\Containers\Users\Helpers;

use Auth;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantsHelper;
use App\Exceptions\Common\NotFoundException;
use App\Exceptions\Common\CreateFailedException;
use App\Exceptions\Common\UpdateFailedException;
use App\Exceptions\Common\NotAllowedException;
use App\Containers\Users\Exceptions\DuplicateEmailException;
use App\Containers\Users\Exceptions\OldPasswordException;
use App\Containers\Users\Exceptions\SameOldPasswordException;
use App\Containers\Users\Exceptions\UpdatePasswordFailedException;
use App\Containers\Users\Messages\Messages;
use App\Containers\Users\Helpers\UserRolesHelper;
use Exception;

class UserHelper
{
    use Messages;

    public static function getMessages()
    {
        $helper = new UserHelper();
        $messages = $helper->messages();
        return $messages;
    }

    /**
     * get user base info (only from users table)
     * 
     * @param int $id
     * @return User $user
     */
    public static function id(int $id)
    {
        try {
            $messages = self::getMessages();
            $user = User::find($id);

            if(!$user) {
                throw new NotFoundException($messages['profile']['exception']);
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
            $messages = self::getMessages();
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
            $messages = self::getMessages();
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
            throw new CreateFailedException($messages['profile']['exception']);
        }

        DB::rollback();
        throw new CreateFailedException($messages['profile']['exception']);
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
            $messages = self::getMessages();
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
                throw new UpdateFailedException($messages['profile']['exception']);
            }
            throw $e;
        }

        DB::rollback();
        throw new UpdateFailedException($messages['profile']['exception']);
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
            $messages = self::getMessages();
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
     * This function receives a user that should have his roles updated
     * so this function checks if the current authenticated user is authorized
     * to update that.
     * and returns true if allowed or throws an exception if not.
     * 
     * @param User $userToBeUpdated
     * @return boolean | NotAllowedException
     */
    public static function authorizedToUpdateUserRoles(User $userToBeUpdated)
    {
        try {
            $userDoingTheUpdate = Auth::user();

            if(!$userToBeUpdated || $userToBeUpdated == null || !$userDoingTheUpdate || $userDoingTheUpdate == null) {
                throw new NotFoundException('User');
            }
            
            $rolesOfUserDoingTheUpdate = $userDoingTheUpdate->roles()->get();
            $highestRoleOfUserDoingTheUpdate = UserRolesHelper::getHighestRole($rolesOfUserDoingTheUpdate);

            $rolesOfUserToBeUpdated = $userToBeUpdated->roles()->get();
            $highestRoleOfUserToBeUpdated = UserRolesHelper::getHighestRole($rolesOfUserToBeUpdated);

            if($highestRoleOfUserDoingTheUpdate->id >= $highestRoleOfUserToBeUpdated->id) {
                // The user doing the update has a role with lower or equal priority to the user being updated
                // So he is not allowed to update his roles,
                throw new NotAllowedException('roles');
            }

            return true;
        } catch (Exception $e) {
            if($e->getMessage() != null) {
                // We have a normal exception
                throw new NotAllowedException('roles');
            }
            throw $e;
        }

        throw new NotAllowedException('roles');
    }

    /**
     * update user by attach permission
     * 
     * @param  User $user
     * @param  int $permissionId
     * @return boolean
     */
    public static function attachPermission(User $user, int $permissionId)
    {
        DB::beginTransaction();

        try {
            $messages = self::getMessages();
            $permission = Permission::find($permissionId);

            if(!$permission) {
                throw new NotFoundException('Permission');
            }

            $user->permissions()->attach($permission);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }

        DB::rollback();
        return false;
    }

    /**
     * update user by detach permission
     * 
     * @param  User $user
     * @param  int $permissionId
     * @return boolean
     */
    public static function detachPermission(User $user, int $permissionId)
    {
        DB::beginTransaction();

        try {
            $messages = self::getMessages();
            $permission = Permission::find($permissionId);

            if(!$permission) {
                throw new NotFoundException('Permission');
            }

            $user->permissions()->detach($permission);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }

        DB::rollback();
        return false;
    }

    /**
     * update user by attach role
     * 
     * @param  User $user
     * @param  int $roleId
     * @return boolean
     */
    public static function attachRole(User $user, int $roleId)
    {
        DB::beginTransaction();

        try {
            $messages = self::getMessages();
            $role = Role::find($roleId);

            if(!$role) {
                throw new NotFoundException('Role');
            }

            $user->roles()->attach($role);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }

        DB::rollback();
        return false;
    }

    /**
     * update user by detach role
     * 
     * @param  User $user
     * @param  int $roleId
     * @return boolean
     */
    public static function detachRole(User $user, int $roleId)
    {
        DB::beginTransaction();

        try {
            $messages = self::getMessages();
            $role = Role::find($roleId);

            if(!$role) {
                throw new NotFoundException('Role');
            }

            $user->roles()->detach($role);

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            return false;
        }

        DB::rollback();
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