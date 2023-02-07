<?php

namespace App\Containers\Auth\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Response\ResponseHelper;
use App\Containers\Auth\Helpers\UserAuthHelper;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use App\Containers\Auth\Messages\Messages;
use App\Containers\Auth\Requests\LoginUserRequest;
use App\Containers\Auth\Requests\ForgotPasswordRequest;
use App\Containers\Auth\Requests\ResetPasswordPasswordRequest;
use Exception;

class PassportAuthController extends Controller
{
    use ResponseHelper, Messages;

    protected $messages = array();

    public function __construct()
    {
        $this->messages = $this->messages();
    }
    
    /**
     * Login user
     * 
     * @param  LoginUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginUserRequest $request)
    {
        try {
            $user_data = $request->all();

            $info = UserAuthHelper::login($user_data);
            
            if($info == null) {
                return $this->return_response($this->not_found, [], $this->messages['LOGIN_FAILED']);
            }
        
            return $this->return_response(
                $this->success,
                $info,
                $this->messages['LOGIN_SUCCESS']
            );
        } catch (Exception $e) {
            return $this->return_response($this->bad_request, [], $this->messages['LOGIN_FAILED'], $e->getMessage());
        }

        return $this->return_response($this->bad_request, [], $this->messages['LOGIN_FAILED']);
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
                    $this->success,
                    [],
                    $this->messages['LOGOUT_SUCCESS']
                );
            }
        } catch (Exception $e) {
            return $this->return_response($this->bad_request, [], $this->messages['LOGOUT_FAILED'], $e->getMessage());
        }

        return $this->return_response($this->bad_request, [], $this->messages['LOGOUT_FAILED']);
    }

    /**
     * Forgot password
     * Send a reset password email to user
     * 
     * @param  ForgotPasswordRequest $request
     * @return \Illuminate\Http\Response
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        $data = $request->all();

        $response = Password::sendResetLink($data);
        
        $message = $response == Password::RESET_LINK_SENT ? 
        $this->messages['FORGOT_EMAIL_SUCCESS'] : $this->messages['FORGOT_EMAIL_FAIL'];

        $status = $response == Password::RESET_LINK_SENT ? $this->success : $this->bad_request;

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
     * @param  ResetPasswordPasswordRequest $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(ResetPasswordPasswordRequest $request)
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
                    $this->unauthorized,
                    [],
                    $message
                );
                break;
            }
            case Password::INVALID_USER: {
                return $this->return_response(
                    $this->bad_request,
                    [],
                    $message
                );
                break;
            }

            case Password::PASSWORD_RESET: {
                $message = $this->messages['RESET_PASSWORD_SUCCESS'];
                return $this->return_response(
                    $this->success,
                    [],
                    $message
                );
                break;
            }
            default: {
                return $this->return_response(
                    $this->bad_request,
                    [],
                    $message
                );
                break;
            }
        }

        return $this->return_response(
            $this->bad_request,
            [],
            $message
        );
    }
}
