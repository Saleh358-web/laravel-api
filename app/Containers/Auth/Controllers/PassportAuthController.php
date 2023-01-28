<?php

namespace App\Containers\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Containers\Auth\Validators\UserLoginValidator;
use App\Helpers\Response\ResponseHelper;
use App\Containers\Auth\Helpers\UserAuthHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Containers\Auth\Messages\Messages;
use Exception;

class PassportAuthController extends Controller
{
    use UserLoginValidator, ResponseHelper, Messages;

    protected $messages = array();

    public function __construct()
    {
        $this->messages = $this->messages();
    }
    
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

            $info = UserAuthHelper::login($user_data);
            
            if($info == null) {
                return $this->return_response(401, [], $this->messages['LOGIN_FAILED']);
            }
        
            return $this->return_response(
                200,
                $info,
                $this->messages['LOGIN_SUCCESS']
            );
        } catch (Exception $e) {
            return $this->return_response(405, [], $this->messages['LOGIN_FAILED'], $e->getMessage());
        }

        return $this->return_response(405, [], $this->messages['LOGIN_FAILED']);
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
            $revoked = UserAuthHelper::logout($request->user());
            
            if($revoked) {
                return $this->return_response(
                    200,
                    [],
                    $this->messages['LOGOUT_SUCCESS']
                );
            }
        } catch (Exception $e) {
            return $this->return_response(400, [], $this->messages['LOGOUT_FAILED'], $e->getMessage());
        }

        return $this->return_response(400, [], $this->messages['LOGOUT_FAILED']);
    }

    /**
     * Forgot password
     * Send a reset password email to user
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(Request $request)
    {
        $data = $request->all();
        $this->forgot_password_validator($data)->validate();

        $response = Password::sendResetLink($data);
        
        $message = $response == Password::RESET_LINK_SENT ? 
        $this->messages['FORGOT_EMAIL_SUCCESS'] : $this->messages['FORGOT_EMAIL_FAIL'];

        $status = $response == Password::RESET_LINK_SENT ? 200 : 400;

        return $this->return_response(
            $status,
            [],
            $message
        );
    }

    /**
     * Reset password
     * Reset password for a user
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request)
    {
        $data = $request->all();
        $this->reset_password_validator($data)->validate();

        $reset_password_status = Password::reset($data, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });

        $message = $this->messages['RESET_PASSWORD_FAIL'];

        switch($reset_password_status) {
            case Password::INVALID_TOKEN: {
                return $this->return_response(
                    401,
                    [],
                    $message
                );
                break;
            }
            case Password::INVALID_USER: {
                return $this->return_response(
                    400,
                    [],
                    $message
                );
                break;
            }

            case Password::PASSWORD_RESET: {
                $message = $this->messages['RESET_PASSWORD_SUCCESS'];
                return $this->return_response(
                    200,
                    [],
                    $message
                );
                break;
            }
            default: {
                return $this->return_response(
                    400,
                    [],
                    $message
                );
                break;
            }
        }

        return $this->return_response(
            400,
            [],
            $message
        );
    }
}
