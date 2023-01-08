<?php

namespace App\Containers\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Containers\Auth\Validators\UserLoginValidator;
use App\Containers\Auth\Helpers\UserTokenHelper;
use App\Helpers\Response\ResponseHelper;
use App\Containers\Auth\Helpers\UserLoginHelper;
use Exception;

class PassportAuthController extends Controller
{
    use UserLoginValidator, ResponseHelper;
    
    /**
     * Login user
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try {
            $user_data = $request->all();

            // returns json error to F.E. if failed
            $this->login_validator($user_data)->validate();

            $info = UserLoginHelper::login($user_data);
            
            if($info == null) {
                return $this->return_response(401, [], 'Unable to login user');
            }
        
            return $this->return_response(
                200,
                $info,
                'User logged successfully'
            );
        } catch (Exception $e) {
            return $this->return_response(405, [], 'Unable to login user', $e->getMessage());
        }

        return $this->return_response(405, [], 'Unable to login user');
    }

    /**
     * Logout user
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        try {
            UserTokenHelper::revoke_token_for_user(auth()->user()->token(), $request->user());
            
            return $this->return_response(
                200,
                [],
                'User logged out successfully'
            );
        } catch (Exception $e) {
            return $this->return_response(400, [], 'Unable to logout user', $e->getMessage());
        }

        return $this->return_response(400, [], 'Unable to logout user');
    }
}
