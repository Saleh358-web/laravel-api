<?php

namespace  App\Containers\Auth\Tests;

use Tests\TestDatabaseTrait;
use Tests\TestCase;
use App\Containers\Users\Helpers\UserHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class PassportAuthControllerTest extends TestCase
{
    private function createUser()
    {
        return UserHelper::create([
            'first_name' => 'Name',
            'last_name' => 'Name',
            'email' => Str::random(5) . '@example.com',
            'password' => 'password',
        ]);
    }
    
    /**
     * Test successful login.
     *
     * @return void
     */
    public function test_login_successful()
    {
        $user = $this->createUser();

        $body = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $response = $this->json('POST', '/api/v1/login', $body, ['Accept' => 'application/json']);

        $response->assertStatus(200)->assertJsonStructure([
            'user' => [
                'id',
                'first_name',
                'last_name',
                'email',
                'email_verified_at',
                'created_at',
                'updated_at',
            ],
             'token',
             'status',
             'message'
        ]);

        $this->assertAuthenticated();
    }

    /**
     * Test fail login.
     *
     * @return void
     */
    public function test_login_fail()
    {
        $user = $this->createUser();

        $body = [
            'email' => $user->email,
            'password' => 'wrong_password'
        ];

        $response = $this->json('POST', '/api/v1/login', $body, ['Accept' => 'application/json']);

        $response->assertStatus(404)->assertJsonStructure([
            'status',
            'message',
            'error'
        ]);
    }

    /**
     * Test successful logout.
     *
     * @return void
     */
    public function test_logout_successful()
    {
        $user = $this->createUser();

        $body = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $response = $this->json('POST', '/api/v1/login', $body, ['Accept' => 'application/json']);

        $response->assertStatus(200);

        $content = json_decode($response->getContent());

        $response = $this->json('POST', '/api/v1/logout', [], [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $content->token,
        ])->assertStatus(200)->assertJsonStructure([
            'status',
            'message'
        ]);
    }

    /**
     * Test fail logout.
     *
     * @return void
     */
    public function test_logout_fail()
    {
        $user = $this->createUser();

        $body = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $response = $this->json('POST', '/api/v1/login', $body, ['Accept' => 'application/json']);

        $response->assertStatus(200);

        $content = json_decode($response->getContent());

        $response = $this->json('POST', '/api/v1/logout', [], [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . 'wrong_token',
        ])->assertStatus(401)->assertJsonStructure([
            'message',
        ]);
    }

    /**
     * Test successful forgot password email.
     *
     * @return void
     */
    public function test_forgot_password_email_successful()
    {
        $user = $this->createUser();

        $response = $this->json(
            'POST',
            '/api/v1/forgotPassword',
            [
                'email' => $user->email
            ],
            [
                'Accept' => 'application/json',
            ])
            ->assertStatus(200)->assertJsonStructure([
                'status',
                'message'
            ]
        );
    }

    /**
     * Test fail forgot password email.
     *
     * @return void
     */
    public function test_forgot_password_email_fail()
    {
        // Wrong email error
        $response = $this->json(
            'POST',
            '/api/v1/forgotPassword',
            [
                'email' => 'wrong_email@example.com'
            ],
            [
                'Accept' => 'application/json',
            ])
            ->assertStatus(400)->assertJsonStructure([
                'status',
                'message',
                'error'
            ]
        );

        // validation no email error
        $response = $this->json(
            'POST',
            '/api/v1/forgotPassword',
            [
            ],
            [
                'Accept' => 'application/json',
            ])
            ->assertStatus(422)->assertJsonStructure([
                'message',
                'errors' => [
                    'email'
                ]
            ]
        );
    }

    /**
     * Test successful forgot password email.
     *
     * @return void
     */
    public function test_reset_password_successful()
    {
        // create a user and set password_resets token
        $user = $this->createUser();
        $token = Password::createToken($user);

        $newPassword = 'New1#Password';

        $response = $this->json(
            'POST',
            '/api/v1/resetPassword',
            [
                'email' => $user->email,
                'token' => $token,
                'password' => $newPassword,
                'password_confirmation' => $newPassword
            ],
            [
                'Accept' => 'application/json',
            ])
            ->assertStatus(200)->assertJsonStructure([
                'status',
                'message',
            ]
        );
    }
}
