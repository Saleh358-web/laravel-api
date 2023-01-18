<?php

namespace  App\Containers\Auth\Tests;

use Tests\TestDatabaseTrait;
use Tests\TestCase;
use App\Containers\Users\Helpers\UserHelper;
use App\Exceptions\Common\CreateFailedException;
use App\Exceptions\Common\UpdateFailedException;
use App\Containers\Users\Exceptions\DuplicateEmailException;
use App\Containers\Users\Exceptions\OldPasswordException;
use App\Containers\Users\Exceptions\SameOldPasswordException;
use App\Containers\Users\Exceptions\UpdatePasswordFailedException;
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
        
        $this->expectException(CreateFailedException::class);

        // Resend the same data to create user should fail
        $user = UserHelper::create($userData);

        $this->assertException($result, 'CreateFailedException');
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
        
        $this->expectException(UpdateFailedException::class);

        $userData['first_name'] = Str::random(500);
        $result = UserHelper::update($user, $userData);

        $this->assertException($result, 'UpdateFailedException');
    }

    /**
     * Test password update successful.
     *
     * @return void
     */
    public function test_helper_update_password_successful()
    {
        $this->setUp();

        $userData = $this->getUserData();
        $user = UserHelper::create($userData);
        
        $data = [
            'old_password' => $userData['password'],
            'password' => 'new_password',
        ];

        $updated = UserHelper::updatePassword($user, $data);
        $this->assertEquals(true, $updated);
    }

    /**
     * Test password update fail as same old password.
     *
     * @return void
     */
    public function test_helper_update_password_fail_same_old_password()
    {
        $this->setUp();

        $userData = $this->getUserData();
        $user = UserHelper::create($userData);
        
        $data = [
            'old_password' => $userData['password'],
            'password' => $userData['password'],
        ];

        $this->expectException(SameOldPasswordException::class);

        $updated = UserHelper::updatePassword($user, $data);

        $this->assertException($result, 'SameOldPasswordException');
    }

    /**
     * Test password update fail as wrong old password.
     *
     * @return void
     */
    public function test_helper_update_password_fail_wrong_old_password()
    {
        $this->setUp();

        $userData = $this->getUserData();
        $user = UserHelper::create($userData);
        
        $data = [
            'old_password' => 'wrong_password',
            'password' => 'new_password',
        ];

        $this->expectException(OldPasswordException::class);

        $updated = UserHelper::updatePassword($user, $data);

        $this->assertException($result, 'OldPasswordException');
    }

    /**
     * Test password update fail general.
     *
     * @return void
     */
    public function test_helper_update_password_fail_general()
    {
        $this->setUp();

        $userData = $this->getUserData();
        $user = UserHelper::create($userData);
        
        $data = [
            'old_password' => $userData['password'],
        ];

        $this->expectException(UpdatePasswordFailedException::class);

        $updated = UserHelper::updatePassword($user, $data);

        $this->assertException($result, 'UpdatePasswordFailedException');
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
