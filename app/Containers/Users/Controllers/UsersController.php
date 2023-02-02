<?php

namespace App\Containers\Users\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Containers\Users\Requests\ActivateDeactivateUsersRequest;
use App\Containers\Users\Messages\Messages;
use App\Helpers\Response\ResponseHelper;
use App\Containers\Users\Helpers\UserHelper;
use App\Containers\Users\Helpers\CrossAuthorizationHelper;
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
                $this->messages['USERS']['GET_ERROR']
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
                $this->messages['USERS']['GET']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['USERS']['GET_ERROR'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['USERS']['GET_ERROR']
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
            return $this->return_response(405, [], $this->messages['USERS']['ATTACH_PERMISSIONS_NOT_ALLOWED']);
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
                $this->messages['USERS']['ATTACH_PERMISSIONS']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['USERS']['ATTACH_PERMISSIONS_FAILED'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['USERS']['ATTACH_PERMISSIONS_FAILED']
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
            return $this->return_response(405, [], $this->messages['USERS']['ATTACH_PERMISSIONS_NOT_ALLOWED']);
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
                $this->messages['USERS']['DETACH_PERMISSIONS']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['USERS']['DETACH_PERMISSIONS_FAILED'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['USERS']['DETACH_PERMISSIONS_FAILED']
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

        $this->addPermission(['name' => 'Attach Roles', 'slug' => 'attach-roles']);

        if (!Auth::user()->allowedTo('attach-roles')) {
            return $this->return_response(405, [], $this->messages['USERS']['ATTACH_ROLES_NOT_ALLOWED']);
        }

        try {
            $data = $request->all();

            $this->roles_user($data)->validate();

            $user = UserHelper::id($data['user_id']);

            // Check if current authenticated user is allowed to to update roles
            $allowedToUpdateRoles = UserHelper::authorizedToUpdateUserRoles($user);

            if($allowedToUpdateRoles) {
                foreach($data['roles'] as $roleId) {
                    UserHelper::attachRole($user, $roleId);
                }
    
                return $this->return_response(
                    200,
                    [],
                    $this->messages['USERS']['ATTACH_ROLES']
                );
            }
            print_r($allowedToUpdateRoles);
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['USERS']['ATTACH_ROLES_FAILED'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['USERS']['ATTACH_ROLES_FAILED']
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
            return $this->return_response(405, [], $this->messages['USERS']['ATTACH_ROLES_NOT_ALLOWED']);
        }

        try {
            $data = $request->all();

            $this->roles_user($data)->validate();

            $user = UserHelper::id($data['user_id']);

            // Check if current authenticated user is allowed to to update roles
            $allowedToUpdateRoles = UserHelper::authorizedToUpdateUserRoles($user);

            if($allowedToUpdateRoles) {
                foreach($data['roles'] as $roleId) {
                    UserHelper::detachRole($user, $roleId);
                }

                return $this->return_response(
                    200,
                    [],
                    $this->messages['USERS']['DETACH_ROLES']
                );
            }
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['USERS']['DETACH_ROLES_FAILED'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['USERS']['DETACH_ROLES_FAILED']
        );
    }

    /**
     * Deactivate Users
     * This function deactivates users
     * revokes his logged in tokens preventing him
     * from exercising his activities to this api
     * 
     * @param ActivateDeactivateUsersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function deactivateUsers(ActivateDeactivateUsersRequest $request)
    {
        $this->messages = $this->messages();

        $this->addPermission(['name' => 'Activate/Deactivate User', 'slug' => 'activate-user']);

        if (!Auth::user()->allowedTo('activate-user')) {
            return $this->return_response(405, [], $this->messages['USERS']['ACTIVATE_DEACTIVATE_USER_NOT_ALLOWED']);
        }

        try {
            $ids = $request->get('user_ids');

            $user = auth()->user();
            $crossAuth = CrossAuthorizationHelper::crossAuthorized($user, $ids);

            if(!$crossAuth) {
                return $this->return_response(405, [], $this->messages['USERS']['CROSS_AUTH_ERROR']);
            }

            foreach($ids as $id) {
                UserHelper::inActivate(UserHelper::id($id));
            }

            return $this->return_response(
                200,
                [],
                $this->messages['USERS']['DEACTIVATE']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['USERS']['DEACTIVATE_ERROR'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(405, [], $this->messages['USERS']['DEACTIVATE_ERROR']);
    }

    /**
     * Activate User
     * This function activates users
     * allowing him to exercise his activities to this api
     * 
     * @param ActivateDeactivateUsersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function activateUsers(ActivateDeactivateUsersRequest $request)
    {
        $this->messages = $this->messages();

        $this->addPermission(['name' => 'Activate/Deactivate User', 'slug' => 'activate-user']);

        if (!Auth::user()->allowedTo('activate-user')) {
            return $this->return_response(405, [], $this->messages['USERS']['ACTIVATE_DEACTIVATE_USER_NOT_ALLOWED']);
        }

        try {
            $ids = $request->get('user_ids');

            $user = auth()->user();
            $crossAuth = CrossAuthorizationHelper::crossAuthorized($user, $ids);

            if(!$crossAuth) {
                return $this->return_response(405, [], $this->messages['USERS']['CROSS_AUTH_ERROR']);
            }

            foreach($ids as $id) {
                UserHelper::activate(UserHelper::id($id));
            }

            return $this->return_response(
                200,
                [],
                $this->messages['USERS']['ACTIVATE']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['USERS']['ACTIVATE_ERROR'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(405, [], $this->messages['USERS']['ACTIVATE_ERROR']);
    }
}