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
use App\Exceptions\Common\NotFoundException;
use App\Helpers\Tests\TestsFacilitator;
use Illuminate\Support\Str;
use App\Helpers\ConstantsHelper;
use App\Helpers\Response\CollectionsHelper;
use App\Helpers\Storage\StoreHelper;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Testing\File;
use Auth;

class UserHelperTest extends TestCase
{
    use TestsFacilitator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /**
     * Test successful id.
     *
     * @return void
     */
    public function test_id_successful()
    {
        $userData = $this->getUserData();
        $user = UserHelper::create($userData);
        $userId = $user->id;

        $user = User::find($userId);
        $userGot = UserHelper::id($userId);

        $this->assertEquals($user, $userGot);
    }

    /**
     * Test fail id.
     *
     * @return void
     */
    public function test_id_fail()
    {
        $this->expectException(NotFoundException::class);

        $userGot = UserHelper::id(53123215);

        $this->assertException($result, 'NotFoundException');
    }

    /**
     * Test successful email.
     *
     * @return void
     */
    public function test_email_successful()
    {
        $userData = $this->getUserData();
        $user = UserHelper::create($userData);

        $userEmail = $user->email;

        $user = User::where('email', $userEmail)->first();
        $userGot = UserHelper::email($userEmail);

        $this->assertEquals($user, $userGot);
    }

    /**
     * Test fail email.
     *
     * @return void
     */
    public function test_email_fail()
    {
        $this->expectException(NotFoundException::class);

        $userGot = UserHelper::email('wrong_email@not_email.com');

        $this->assertException($result, 'NotFoundException');
    }

    /**
     * Test successful profile.
     *
     * @return void
     */
    public function test_profile_successful()
    {
        $userCreatedWithRaw = $this->createUser();
        $user = $userCreatedWithRaw['user'];
        $content = $this->login(null, $userCreatedWithRaw['userRawData']);

        $profile = UserHelper::profile();
        $user = Auth::user()->load(['roles', 'profileImage']);
        $this->assertEquals($user, $profile);
    }

    /**
     * Test fail profile.
     *
     * @return void
     */
    public function test_profile_fail()
    {
        $this->expectException(NotFoundException::class);
        $profile = UserHelper::profile();
        $this->assertException($result, 'NotFoundException');
    }

    /**
     * Test successful in_activate.
     *
     * @return void
     */
    public function test_in_activate_successful()
    {
        $userCreatedWithRaw = $this->createUser();
        $user = $userCreatedWithRaw['user'];

        // login user to have at least one login token for him
        $content = $this->login(null, $userCreatedWithRaw['userRawData']);

        $result = UserHelper::inactivate($user);
        $this->assertEquals($result, true);
        
        $checkUser = User::find($user->id);
        $this->assertEquals($checkUser->active, false);
        $this->assertEquals($checkUser->tokens->where('revoked', 0)->count(), 0);
    }

    /**
     * Test successful activate.
     *
     * @return void
     */
    public function test_activate_successful()
    {
        $userCreatedWithRaw = $this->createUser();
        $user = $userCreatedWithRaw['user'];

        // login user to have at least one login token for him
        $content = $this->login(null, $userCreatedWithRaw['userRawData']);

        $inActivate = UserHelper::inactivate($user); // the create function above creates active user
        
        // assert that user is truly inactivated
        $this->assertEquals($inActivate, true);
        $checkUser = User::find($user->id);
        $this->assertEquals($checkUser->active, false);
        $this->assertEquals($checkUser->tokens->where('revoked', 0)->count(), 0);

        $result = UserHelper::activate($user);
        $this->assertEquals($result, true);
        $checkUser = User::find($user->id);
        $this->assertEquals($checkUser->active, true);
    }

    /**
     * Test successful getAll.
     *
     * @return void
     */
    public function test_getAll_successful()
    {
        $paginationCount = ConstantsHelper::getPagination(null);
        $users = User::with(['roles', 'permissions', 'profileImage'])
        ->get()->each(function (User $user) {
            if($user->profileImage) {
                $user->profileImage->link = StoreHelper::getFileLink($user->profileImage->link);
            }
        });
        $users = CollectionsHelper::paginate($users, $paginationCount);
        $users = json_decode(json_encode($users));

        $result = UserHelper::getAll();
        $this->assertEquals($users, $result);
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

    /**
     * Test update profile photo successful.
     *
     * @return void
     */
    public function test_update_profile_photo_successful()
    {
        $image = File::image('image.png', 400, 100);
        $userData = $this->getUserData();
        $user = UserHelper::create($userData);

        // Result should be the newly created image model App\Models\Image
        $result = UserHelper::updateProfilePhoto($user, $image);

        $user = User::find($user->id);
        $this->assertEquals($user->profile_image, $result->id);
    }

    /**
     * Test delete user successful.
     *
     * @return void
     */
    public function test_delete_user_successful()
    {
        // create user with profile image
        $image = File::image('image.png', 400, 100);
        $userData = $this->getUserData();
        $user = UserHelper::create($userData);
        $result = UserHelper::updateProfilePhoto($user, $image);
        $user = User::find($user->id);
        $this->assertEquals($user->profile_image, $result->id);

        // attach a role
        $role = Role::where('slug', 'user')->first();
        $user->roles()->attach($role);
        $this->assertEquals($user->roles()->count(), 1);

        // attach a permission
        $permission = Permission::first();
        $user->permissions()->attach($permission);
        $this->assertEquals($user->permissions()->count(), 1);
        
        $result = UserHelper::deleteUser($user);
        $this->assertEquals($result, true);
        $checkUser = User::where('id', $user->id)->withTrashed()->first();
        $this->assertEquals($checkUser->active, false);
        $this->assertEquals($user->roles()->count(), 0);
        $this->assertEquals($user->permissions()->count(), 0);
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
