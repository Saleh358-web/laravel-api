<?php

namespace App\Containers\Users\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Containers\Users\Messages\Messages;
use App\Helpers\Response\ResponseHelper;
use App\Containers\Users\Helpers\UserHelper;
use Exception;
use Auth;

class UsersController extends Controller
{
    use ResponseHelper, Messages;

    /**
     * Get all users
     * 
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        $messages = $this->messages();

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
}