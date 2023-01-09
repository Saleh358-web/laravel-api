<?php

namespace  App\Containers\Auth\Tests;

use Tests\TestDatabaseTrait;
use Tests\TestCase;
use App\Containers\Users\Helpers\UserHelper;
use App\Containers\Auth\Helpers\UserAuthHelper;
use Illuminate\Support\Str;

class UserAuthHelperTest extends TestCase
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
    public function test_helper_login_successful()
    {
        $user = $this->createUser();

        $body = [
            'email' => $user->email,
            'password' => 'password'
        ];

        $result = UserAuthHelper::login($body);
        $this->assertEquals(isset($result['token']), true);
        $this->assertEquals([
            'user' => auth()->user(),
            'token' => $result['token']
        ], $result);
    }

    /**
     * Test fail login.
     *
     * @return void
     */
    public function test_helper_login_fail()
    {
        $user = $this->createUser();

        $body = [
            'email' => $user->email,
            'password' => 'wrong_password'
        ];

        $result = UserAuthHelper::login($body);
        $this->assertEquals(null, $result);
    }
}
