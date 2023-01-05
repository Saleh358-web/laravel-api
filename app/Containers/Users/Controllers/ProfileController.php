<?php

namespace App\Containers\Users\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Containers\Users\Messages\Messages;
use App\Helpers\Response\ResponseHelper;
use Auth;

class ProfileController extends Controller
{
    use ResponseHelper, Messages;

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
            return $this->return_response(405, [], $messages['profile']['error']);
        }

        return $this->return_response(405, [], $messages['profile']['error']);
    }
}
