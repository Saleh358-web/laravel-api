<?php

namespace  App\Containers\Auth\Tests;

use Tests\TestDatabaseTrait;
use Tests\TestCase;
use App\Containers\Users\Helpers\UserHelper;
use App\Containers\Users\Exceptions\CreateUserFailedException;
use App\Containers\Users\Exceptions\UpdateUserFailedException;
use App\Containers\Users\Exceptions\DuplicateEmailException;
use Illuminate\Support\Str;
use App\Models\User;

class UserHelperTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /**
     * Test successful create.
     *
     * @return void
     */
    public function test_helper_create_successful()
    {
        $userData = $this->getUserData();

        $user = UserHelper::create($userData);

        $dbUser = User::orderBy('id', 'desc')->first();

        $userMapped = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ];

        $dbUserMapped = [
            'id' => $dbUser->id,
            'first_name' => $dbUser->first_name,
            'last_name' => $dbUser->last_name,
            'email' => $dbUser->email,
        ];

        $this->assertEquals($userMapped, $dbUserMapped);
    }

    /**
     * Test fail create.
     *
     * @return void
     */
    public function test_helper_create_fail()
    {
        $this->setUp();

        $userData = $this->getUserData();

        // This should create a new user 
        $result = UserHelper::create($userData);
        
        $this->expectException(CreateUserFailedException::class);

        // Resend the same data to create user should fail
        $user = UserHelper::create($userData);

        $this->assertException($result, 'CreateUserFailedException');
    }

    /**
     * Test successful update.
     *
     * @return void
     */
    public function test_helper_update_successful()
    {
        $userData = $this->getUserData();

        $user = UserHelper::create($userData);

        $userData['first_name'] = 'New Name';

        $user = UserHelper::update($user, $userData);

        $dbUser = User::find($user->id);

        $userMapped = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
        ];

        $dbUserMapped = [
            'id' => $dbUser->id,
            'first_name' => $dbUser->first_name,
            'last_name' => $dbUser->last_name,
            'email' => $dbUser->email,
        ];

        $this->assertEquals($userMapped, $dbUserMapped);
    }

    /**
     * Test email fail update.
     *
     * @return void
     */
    public function test_helper_update_email_fail()
    {
        $this->setUp();

        $userData = $this->getUserData();
        $user1 = UserHelper::create($userData);

        $userData = $this->getUserData();
        $user2 = UserHelper::create($userData);
        
        $this->expectException(DuplicateEmailException::class);

        $userData['email'] = $user1->email;
        $result = UserHelper::update($user2, $userData);

        $this->assertException($result, 'DuplicateEmailException');
    }

    /**
     * Test general fail update.
     *
     * @return void
     */
    public function test_helper_update_general_fail()
    {
        $this->setUp();

        $userData = $this->getUserData();
        $user = UserHelper::create($userData);
        
        $this->expectException(UpdateUserFailedException::class);

        $userData['first_name'] = Str::random(500);
        $result = UserHelper::update($user, $userData);

        $this->assertException($result, 'UpdateUserFailedException');
    }

    private function getUserData()
    {
        return [
            'first_name' => 'Name',
            'last_name' => 'Name',
            'email' => Str::random(5) . '@example.com',
            'password' => 'password',
        ];
    }
}
