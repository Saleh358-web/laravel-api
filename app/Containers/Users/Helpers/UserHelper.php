<?php

namespace App\Containers\Users\Helpers;

use Auth;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Models\Image;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\ConstantsHelper;
use Illuminate\Support\Facades\Log;
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
use App\Helpers\Storage\StoreHelper;
use App\Helpers\Storage\LocalStore;
use App\Helpers\Response\CollectionsHelper;
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
        } catch (Exception $e) {
            Log::error('User not found - UserHelper::id(' . $id . ')');
            throw new NotFoundException($messages['profile']['exception']);
        }

        throw new NotFoundException($messages['profile']['exception']);
    }

    /**
     * This function returns the current authenticated user profile information
     * 
     * @return User $user
     */
    public static function profile()
    {
        try {
            $user = Auth::user();

            if(!$user || $user == null) {
                throw new NotFoundException('User');
            }

            $user = $user->load(['roles', 'profileImage']);

            if(isset($user->profileImage)) {
                $user->profileImage->link = StoreHelper::getFileLink($user->profileImage->link);
            }
    
            Log::info('User profile returned');
    
            return $user;
        } catch (Exception $e) {
            Log::error('Get user profile failed - UserHelper::profile()');
            throw $e;
        }
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

            $users = User::with(['roles', 'permissions', 'profileImage'])
            ->get()->each(function (User $user) {
                if($user->profileImage) {
                    $user->profileImage->link = StoreHelper::getFileLink($user->profileImage->link);
                }
            });

            $users = CollectionsHelper::paginate($users, $paginationCount);
            
            $users = json_decode(json_encode($users)); // This will change its type to StdClass

            Log::info('User returned successfully');

            return $users;
        } catch (\Exception $e) {
            Log::error('Get users failed - UserHelper::getAll()');
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

            Log::info('User created successfully');
            
            return $user;
        } catch (Exception $e) {
            Log::error('User create failed on UserHelper::create() with data: ' . json_encode($data));
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

            Log::info('User data updated successfully');
            
            $user = User::find($user->id);
            return $user;
        } catch (Exception $e) {
            Log::error('User data updated failed - UserHelper::update()');
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
            Log::error('Update password failed - UserHelper::updatePassword');
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
     * This function updates the profile photo of the user
     * 
     * @param User $user
     * @param $photo
     * @param $photoSize
     * @return Image $image | UpdateFailedException
     */
    public static function updateProfilePhoto(User $user, $photo, $photoSize = null)
    {
        DB::beginTransaction();
        try {
            $messages = self::getMessages();

            $subPath = 'uploads/images/users/' . $user->id;

            $path = StoreHelper::storeFile($photo, $subPath);

            $image = $user->profileImage()->first();

            if($image) {
                StoreHelper::deleteFile($image->link);
                $image->link = $path;
                $image->size = $photoSize;
                $image->save();
            } else {
                $image = Image::create([
                    'link' => $path,
                    'size' => $photoSize
                ]);
            }

            $user->profile_image = $image->id;
            $user->save();

            DB::commit();
            Log::info('User profile photo uploaded successfully');
            return $image;
        } catch (Exception $e) {
            Log::error('User profile photo upload failed - UserHelper::updateProfilePhoto()');
            DB::rollback();
            if($e->getMessage() != null) {
                // We have a normal exception
                throw new UpdateFailedException($messages['profile']['exception']);
            }
            throw $e;
        }

        throw new UpdateFailedException($messages['profile']['exception']);
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
            Log::error('Authorization failed on UserHelper::authorizedToUpdateUserRoles');
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

            Log::info('Permission attached successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Permission attach failed - UserHelper::attachPermission');
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
            Log::info('Permission detached successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Permission detach failed - UserHelper::detachPermission');
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
            Log::info('Role attached successfully');
            return true;
        } catch (Exception $e) {
            Log::info('Role attach failed - UserHelper::attachRole');
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
            Log::info('Role detached successfully');
            return true;
        } catch (Exception $e) {
            Log::info('Role detach failed - UserHelper::detachRole');
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