<?php

namespace App\Containers\Users\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Helpers\Database\PermissionsHelper;
use App\Helpers\Response\ResponseHelper;
use App\Requests\PaginationRequest;

use App\Containers\Users\Helpers\CrossAuthorizationHelper;
use App\Containers\Users\Validators\UsersValidators;
use App\Containers\Users\Requests\UserArraysRequest;
use App\Containers\Users\Requests\CreateUserRequest;
use App\Containers\Users\Messages\Messages;
use App\Containers\Users\Helpers\UserHelper;

use Exception;
use Auth;

class UsersController extends Controller
{
    use ResponseHelper, Messages, UsersValidators;

    protected $messages = array();

    public function __construct()
    {
        $this->messages = $this->messages();
    }

    /**
     * Get all users
     * 
     * @param PaginationRequest $request
     * @return \Illuminate\Http\Response
     */
    public function get(PaginationRequest $request)
    {
        if (!Auth::user()->allowedTo('get-users')) {
            return $this->return_response(
                $this->not_allowed,
                [],
                $this->messages['USERS']['GET_ERROR']
            );
        }

        try {
            $data = UserHelper::getAll($request->get('pagination'));
            
            $info = [
                'meta' => $this->metaData($data),
                'users' => $data->data
            ];

            return $this->return_response(
                $this->success,
                $info,
                $this->messages['USERS']['GET']
            );
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['GET_ERROR'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            $this->bad_request,
            [],
            $this->messages['USERS']['GET_ERROR']
        );
    }

    /**
     * Create a new user
     * 
     * @param CreateUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateUserRequest $request)
    {
        $this->messages = $this->messages();

        if (!Auth::user()->allowedTo('create-users')) {
            return $this->return_response($this->not_allowed, [], $this->messages['USERS']['CREATE_USER_NOT_ALLOWED']);
        }

        try {
            $data = $request->all();
            $user = UserHelper::create($data);

            return $this->return_response(
                $this->success,
                ['user' => $user],
                $this->messages['USERS']['CREATE_USER_SUCCESS']
            );
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['CREATE_USER_FAILED'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            $this->bad_request,
            [],
            $this->messages['USERS']['CREATE_USER_FAILED']
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

        if (!Auth::user()->allowedTo('attach-permissions')) {
            return $this->return_response($this->not_allowed, [], $this->messages['USERS']['ATTACH_PERMISSIONS_NOT_ALLOWED']);
        }

        try {
            $data = $request->all();

            $this->permissions_user($data)->validate();

            $user = UserHelper::id($data['user_id']);

            foreach($data['permissions'] as $permissionId) {
                UserHelper::attachPermission($user, $permissionId);
            }

            return $this->return_response(
                $this->success,
                [],
                $this->messages['USERS']['ATTACH_PERMISSIONS']
            );
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['ATTACH_PERMISSIONS_FAILED'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            $this->bad_request,
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

        if (!Auth::user()->allowedTo('attach-permissions')) {
            return $this->return_response($this->not_allowed, [], $this->messages['USERS']['ATTACH_PERMISSIONS_NOT_ALLOWED']);
        }

        try {
            $data = $request->all();

            $this->permissions_user($data)->validate();

            $user = UserHelper::id($data['user_id']);

            foreach($data['permissions'] as $permissionId) {
                UserHelper::detachPermission($user, $permissionId);
            }

            return $this->return_response(
                $this->success,
                [],
                $this->messages['USERS']['DETACH_PERMISSIONS']
            );
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['DETACH_PERMISSIONS_FAILED'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            $this->bad_request,
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

        if (!Auth::user()->allowedTo('attach-roles')) {
            return $this->return_response($this->not_allowed, [], $this->messages['USERS']['ATTACH_ROLES_NOT_ALLOWED']);
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
                    $this->success,
                    [],
                    $this->messages['USERS']['ATTACH_ROLES']
                );
            }
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['ATTACH_ROLES_FAILED'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            $this->bad_request,
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

        if (!Auth::user()->allowedTo('attach-roles')) {
            return $this->return_response($this->not_allowed, [], $this->messages['USERS']['ATTACH_ROLES_NOT_ALLOWED']);
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
                    $this->success,
                    [],
                    $this->messages['USERS']['DETACH_ROLES']
                );
            }
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['DETACH_ROLES_FAILED'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            $this->bad_request,
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
     * @param UserArraysRequest $request
     * @return \Illuminate\Http\Response
     */
    public function deactivateUsers(UserArraysRequest $request)
    {
        $this->messages = $this->messages();

        if (!Auth::user()->allowedTo('activate-user')) {
            return $this->return_response($this->not_allowed, [], $this->messages['USERS']['ACTIVATE_DEACTIVATE_USER_NOT_ALLOWED']);
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
                $this->success,
                [],
                $this->messages['USERS']['DEACTIVATE']
            );
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['DEACTIVATE_ERROR'],
                $this->exception_message($e)
            );
        }

        return $this->return_response($this->bad_request, [], $this->messages['USERS']['DEACTIVATE_ERROR']);
    }

    /**
     * Activate User
     * This function activates users
     * allowing him to exercise his activities to this api
     * 
     * @param UserArraysRequest $request
     * @return \Illuminate\Http\Response
     */
    public function activateUsers(UserArraysRequest $request)
    {
        $this->messages = $this->messages();

        if (!Auth::user()->allowedTo('activate-user')) {
            return $this->return_response($this->not_allowed, [], $this->messages['USERS']['ACTIVATE_DEACTIVATE_USER_NOT_ALLOWED']);
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
                $this->success,
                [],
                $this->messages['USERS']['ACTIVATE']
            );
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['ACTIVATE_ERROR'],
                $this->exception_message($e)
            );
        }

        return $this->return_response($this->bad_request, [], $this->messages['USERS']['ACTIVATE_ERROR']);
    }

    /**
     * Delete Users
     * This function deletes users
     * revokes his logged in tokens preventing him
     * from exercising his activities to this api
     * and prevent users from restoring his profile.
     * 
     * To restore request the restore deleted route
     * 
     * @param UserArraysRequest $request
     * @return \Illuminate\Http\Response
     */
    public function deleteUsers(UserArraysRequest $request)
    {
        $this->messages = $this->messages();

        if (!Auth::user()->allowedTo('delete-user')) {
            return $this->return_response($this->not_allowed, [], $this->messages['USERS']['DELETE_USER_NOT_ALLOWED']);
        }

        try {
            $ids = $request->get('user_ids');

            $user = auth()->user();
            $crossAuth = CrossAuthorizationHelper::crossAuthorized($user, $ids);

            if(!$crossAuth) {
                return $this->return_response(405, [], $this->messages['USERS']['CROSS_AUTH_ERROR']);
            }

            foreach($ids as $id) {
                UserHelper::deleteUser(UserHelper::id($id));
            }

            return $this->return_response(
                $this->success,
                [],
                $this->messages['USERS']['DELETE_SUCCESSFUL']
            );
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['DELETE_ERROR'],
                $this->exception_message($e)
            );
        }

        return $this->return_response($this->bad_request, [], $this->messages['USERS']['DELETE_ERROR']);
    }

    /**
     * This functions returns the profiles of the deleted users
     * Being deleted only by soft deletes
     *
     * @param PaginationRequest $request 
     * @return \Illuminate\Http\Response
     */
    public function getDeletedUsers(PaginationRequest $request)
    {
        if (!Auth::user()->allowedTo('get-deleted-users')) {
            return $this->return_response(
                $this->not_allowed,
                [],
                $this->messages['USERS']['GET_ERROR']
            );
        }

        try {
            $data = UserHelper::getDeleted($request->get('pagination'));
            
            $info = [
                'meta' => $this->metaData($data),
                'users' => $data->data
            ];

            return $this->return_response(
                $this->success,
                $info,
                $this->messages['USERS']['GET']
            );
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['GET_ERROR'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            $this->bad_request,
            [],
            $this->messages['USERS']['GET_ERROR']
        );
    }

    /**
     * This functions returns the in activated users
     * 
     * @param PaginationRequest $request
     * @return \Illuminate\Http\Response
     */
    public function getInActiveUsers(PaginationRequest $request)
    {
        if (!Auth::user()->allowedTo('get-inactive-users')) {
            return $this->return_response(
                $this->not_allowed,
                [],
                $this->messages['USERS']['GET_ERROR']
            );
        }

        try {
            $data = UserHelper::getInActivated($request->get('pagination'));
            
            $info = [
                'meta' => $this->metaData($data),
                'users' => $data->data
            ];

            return $this->return_response(
                $this->success,
                $info,
                $this->messages['USERS']['GET']
            );
        } catch (Exception $e) {
            return $this->return_response(
                $this->bad_request,
                [],
                $this->messages['USERS']['GET_ERROR'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            $this->bad_request,
            [],
            $this->messages['USERS']['GET_ERROR']
        );
    }
}