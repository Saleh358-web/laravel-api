<?php

namespace  App\Containers\Auth\Tests;

use Tests\TestDatabaseTrait;
use Tests\TestCase;
use App\Containers\Users\Helpers\UserHelper;
use Illuminate\Support\Str;
use App\Helpers\Tests\TestsFacilitator;

class ProfileControllerTest extends TestCase
{
    use TestsFacilitator;

    /**
     * Test successful get profile.
     *
     * @return void
     */
    public function test_get_successful()
    {
        $userCreatedWithRaw = $this->createUser();

        $user = $userCreatedWithRaw['user'];

        $content = $this->login(null, $userCreatedWithRaw['userRawData']);

        $response = $this->json(
            'GET',
            '/api/v1/profile',
            [],
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $content->token
            ]);

        $response->assertStatus(200)->assertJsonStructure([
            'user',
            'status',
            'message'
        ]);
    }

    /**
     * Test fail get profile.
     *
     * @return void
     */
    public function test_get_fail()
    {
        $response = $this->json(
            'GET',
            '/api/v1/profile',
            [],
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . 'wrong_token'
            ]
        );

        $response->assertStatus(401)->assertJsonStructure([
            'message'
        ]);
    }

    /**
     * Test successful update profile.
     *
     * @return void
     */
    public function test_update_successful()
    {
        $userCreatedWithRaw = $this->createUser();

        $user = $userCreatedWithRaw['user'];

        $content = $this->login(null, $userCreatedWithRaw['userRawData']);

        $body = [
            'first_name' => Str::random(5),
            'last_name' => Str::random(5),
            'email' => Str::random(5) . '@example.com',
        ];

        $response = $this->json(
            'PUT',
            '/api/v1/profile',
            $body,
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $content->token
            ]);

        $response->assertStatus(200)->assertJsonStructure([
            'user',
            'status',
            'message'
        ]);
    }

    /**
     * Test fail update profile.
     *
     * @return void
     */
    public function test_update_fail_email()
    {
        $userCreatedWithRaw = $this->createUser();

        $user = $userCreatedWithRaw['user'];

        $content = $this->login(null, $userCreatedWithRaw['userRawData']);

        $user2CreatedWithRaw = $this->createUser();

        $body = [
            'first_name' => Str::random(5),
            'last_name' => Str::random(5),
            'email' => $user2CreatedWithRaw['userRawData']['email'],
        ];

        $response = $this->json(
            'PUT',
            '/api/v1/profile',
            $body,
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $content->token
            ]);

        $response->assertStatus(405);
    }
}
