<?php

namespace App\Containers\Users\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Containers\Users\Messages\Messages;
use App\Helpers\Response\ResponseHelper;
use App\Containers\Users\Helpers\UserHelper;
use App\Containers\Users\Validators\UsersValidators;
use App\Helpers\Database\PermissionsHelper;
use Exception;
use Auth;

class UsersController extends Controller
{
    use ResponseHelper, Messages, PermissionsHelper, UsersValidators;

    protected $messages = array();

    public function __construct()
    {
        $this->messages = $this->messages();
    }

    /**
     * Get all users
     * 
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        $this->addPermission(['name' => 'Get all users', 'slug' => 'get-users']);

        if (!Auth::user()->allowedTo('get-users')) {
            return $this->return_response(
                405,
                [],
                $this->messages['users']['get_error']
            );
        }

        try {
            $data = UserHelper::getAll();
            
            $info = [
                'meta' => $this->metaData($data),
                'users' => $data->data
            ];

            return $this->return_response(
                200,
                $info,
                $this->messages['users']['get']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['users']['get_error'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['users']['get_error']
        );
    }

    /**
     * Add Permissions
     * This function adds permissions to users
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addPermissionsToUser(Request $request)
    {
        $this->messages = $this->messages();

        $this->addPermission(['name' => 'Attach Permissions', 'slug' => 'attach-permissions']);

        if (!Auth::user()->allowedTo('attach-permissions')) {
            return $this->return_response(405, [], $this->messages['users']['attach_permissions_not_allowed']);
        }

        try {
            $data = $request->all();

            $this->permissions_user($data)->validate();

            $user = UserHelper::id($data['user_id']);

            foreach($data['permissions'] as $permissionId) {
                UserHelper::attachPermission($user, $permissionId);
            }

            return $this->return_response(
                200,
                [],
                $this->messages['users']['attach_permissions']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['users']['attach_permissions_failed'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['users']['attach_permissions_failed']
        );
    }

    /**
     * Remove Permissions
     * This function removes permissions to users
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function removePermissionsToUser(Request $request)
    {
        $this->messages = $this->messages();

        $this->addPermission(['name' => 'Attach Permissions', 'slug' => 'attach-permissions']);

        if (!Auth::user()->allowedTo('attach-permissions')) {
            return $this->return_response(405, [], $this->messages['users']['attach_permissions_not_allowed']);
        }

        try {
            $data = $request->all();

            $this->permissions_user($data)->validate();

            $user = UserHelper::id($data['user_id']);

            foreach($data['permissions'] as $permissionId) {
                UserHelper::detachPermission($user, $permissionId);
            }

            return $this->return_response(
                200,
                [],
                $this->messages['users']['detach_permissions']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['users']['attach_permissions_failed'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['users']['attach_permissions_failed']
        );
    }

    /**
     * Add Roles
     * This function adds roles to users
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function addRolesToUser(Request $request)
    {
        $this->messages = $this->messages();

        $this->addPermission(['name' => 'Attach Roles', 'slug' => 'roles-permissions']);

        if (!Auth::user()->allowedTo('attach-roles')) {
            return $this->return_response(405, [], $this->messages['users']['attach_roles_not_allowed']);
        }

        try {
            $data = $request->all();

            $this->roles_user($data)->validate();

            $user = UserHelper::id($data['user_id']);

            foreach($data['roles'] as $roleId) {
                UserHelper::attachRole($user, $roleId);
            }

            return $this->return_response(
                200,
                [],
                $this->messages['users']['attach_roles']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['users']['attach_roles_failed'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['users']['attach_roles_failed']
        );
    }

    /**
     * Remove Roles
     * This function removes roles to users
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function removeRolesToUser(Request $request)
    {
        $this->messages = $this->messages();

        $this->addPermission(['name' => 'Attach Roles', 'slug' => 'attach-roles']);

        if (!Auth::user()->allowedTo('attach-roles')) {
            return $this->return_response(405, [], $this->messages['users']['attach_roles_not_allowed']);
        }

        try {
            $data = $request->all();

            $this->roles_user($data)->validate();

            $user = UserHelper::id($data['user_id']);

            foreach($data['roles'] as $roleId) {
                UserHelper::detachRole($user, $roleId);
            }

            return $this->return_response(
                200,
                [],
                $this->messages['users']['detach_roles']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['users']['attach_roles_failed'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['users']['attach_roles_failed']
        );
    }
}