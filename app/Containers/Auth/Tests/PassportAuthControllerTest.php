<?php

namespace  App\Containers\Auth\Tests;

use Tests\TestDatabaseTrait;
use Tests\TestCase;

class PassportAuthControllerTest extends TestCase
{
    /**
     * Test successful login.
     *
     * @return void
     */
    public function test_login_successful()
    {
        $body = [
            'email' => 'admin@example.com',
            'password' => 'admin123456'
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
     * Test successful logout.
     *
     * @return void
     */
    public function test_logout_successful()
    {
        $body = [
            'email' => 'admin@example.com',
            'password' => 'admin123456'
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
}
