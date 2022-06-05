<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\User\UserValidator;
use App\Helpers\User\UserTokenHelper;
use App\Helpers\Response\ResponseHelper;
use App\Helpers\User\UserCreateHelper;
use App\Helpers\User\UserLoginHelper;

class PassportAuthController extends Controller
{
    use UserValidator, ResponseHelper;
    
    /**
     * Login
     */
    public function login(Request $request)
    {
        try {
            $user_data = $request->all();

            // returns json error to F.E. if failed
            $this->login_validator($user_data)->validate();

            $info = UserLoginHelper::login($user_data);
            
            if($info == null) {
                return $this->return_response(401, 'Unable to login user');
            }
        
            return $this->return_response(
                200,
                $info,
                'User logged successfully'
            );
        } catch (Exception $e) {
            return $this->return_response(405, 'Unable to login user', $e->getMessage());
        }

        return $this->return_response(405, 'Unable to login user');
    }
}
