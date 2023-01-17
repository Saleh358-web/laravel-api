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

    /**
     * Get all users
     * 
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        $messages = $this->messages();

        $this->addPermission(['name' => 'Get all users', 'slug' => 'get-users']);

        if (!Auth::user()->allowedTo('get-users')) {
            return $this->return_response(405, [], $messages['users']['get_error']);
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
                $messages['users']['get']
            );
        } catch (Exception $e) {
            return $this->return_response(405, [], $messages['users']['get_error'], $this->exception_message($e));
        }

        return $this->return_response(405, [], $messages['users']['get_error']);
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
        $messages = $this->messages();

        $this->addPermission(['name' => 'Attach Permissions', 'slug' => 'attach-permissions']);

        if (!Auth::user()->allowedTo('attach-permissions')) {
            return $this->return_response(405, [], $messages['users']['attach_permissions_not_allowed']);
        }

        try {
            $data = $request->all();

            $this->add_permissions_to_user($data)->validate();

            $user = UserHelper::id($data['user_id']);

            $info = [
                
            ];

            return $this->return_response(
                200,
                $info,
                $messages['users']['attach_permissions']
            );
        } catch (Exception $e) {
            return $this->return_response(405, [], $messages['users']['attach_permissions_failed'], $this->exception_message($e));
        }

        return $this->return_response(405, [], $messages['users']['attach_permissions_failed']);
    }
}