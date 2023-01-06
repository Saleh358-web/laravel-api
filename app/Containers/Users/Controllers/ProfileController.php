<?php

namespace App\Containers\Users\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Containers\Users\Messages\Messages;
use App\Containers\Users\Validators\ProfileValidators;
use App\Helpers\Response\ResponseHelper;
use App\Containers\Users\Helpers\UserHelper;
use Exception;
use Auth;

class ProfileController extends Controller
{
    use ResponseHelper, Messages, ProfileValidators;

    /**
     * Get logged in user profile
     * 
     * @return \Illuminate\Http\Response
     */
    public function get()
    {
        $messages = $this->messages();

        try {
            $info = [
                'user' => Auth::user()
            ];

            return $this->return_response(
                200,
                $info,
                $messages['profile']['get']
            );
        } catch (\Exception $e) {
            return $this->return_response(405, [], $messages['profile']['get_error']);
        }

        return $this->return_response(405, [], $messages['profile']['get_error']);
    }

    /**
     * Update logged in user profile
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $messages = $this->messages();

        try {
            $data = $request->all();
            $this->update_validator($data)->validate();

            $user = Auth::user();
            $updateUser = UserHelper::update($user, $data);

            return $this->return_response(
                200,
                [$updateUser],
                $messages['profile']['update']
            );
        } catch (Exception $e) {
            return $this->return_response(405, [], $messages['profile']['update_error'], $e->error());
        }

        return $this->return_response(405, [], $messages['profile']['update_error']);
    }
}
