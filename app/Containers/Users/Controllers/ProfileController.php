<?php

namespace App\Containers\Users\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Containers\Users\Messages\Messages;
use App\Containers\Users\Validators\ProfileValidators;
use App\Helpers\Response\ResponseHelper;
use App\Containers\Users\Helpers\UserHelper;
use App\Helpers\Storage\StoreHelper;
use Exception;
use Auth;

class ProfileController extends Controller
{
    use ResponseHelper, Messages, ProfileValidators;

    protected $messages = array();

    public function __construct()
    {
        $this->messages = $this->messages();
    }

    /**
     * Get logged in user profile
     * 
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        try {
            $info = [
                'user' => Auth::user()
            ];

            return $this->return_response(
                200,
                $info,
                $this->messages['profile']['get']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['profile']['get_error'],
                $this->exception_message($e)
            );
        }
        return $this->return_response(
            405,
            [],
            $this->messages['profile']['get_error']
        );
    }

    /**
     * Update logged in user profile
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $this->update_validator($data)->validate();

            $user = Auth::user();
            $updateUser = UserHelper::update($user, $data);

            $data = [
                'user' => $updateUser
            ];

            return $this->return_response(
                200,
                $data,
                $this->messages['profile']['update']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['profile']['update_error'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['profile']['update_error']
        );
    }

    /**
     * Update logged in user password
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        try {
            $data = $request->all();
            $this->update_password_validator($data)->validate();

            $user = Auth::user();
            $updated = UserHelper::updatePassword($user, $data);

            if($updated) {
                return $this->return_response(
                    200,
                    [],
                    $this->messages['profile']['password']
                );
            }
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['profile']['password_error'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['profile']['password_error']
        );
    }

    /**
     * Uploads profile photo
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function updatePhoto(Request $request)
    {
        try {
            $data = $request->all();
            // $this->update_validator($data)->validate();

            $user = Auth::user();

            $path = StoreHelper::storeFile($request->file('photo'), 'images/users/' . $user->id);

            return $this->return_response(
                200,
                ['link' => asset($path)],
                $this->messages['profile']['update']
            );
        } catch (Exception $e) {
            return $this->return_response(
                405,
                [],
                $this->messages['profile']['update_error'],
                $this->exception_message($e)
            );
        }

        return $this->return_response(
            405,
            [],
            $this->messages['profile']['update_error']
        );
    }
}
