<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admin_role = Role::where('slug', 'super-admin')->first();
        $user_role = Role::where('slug', 'user')->first();

        $admin = User::create([
            'first_name' => 'Admin',
            'last_name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
        ]);
        $admin->roles()->attach($admin_role);

        $user = User::create([
            'first_name' => 'User',
            'last_name' => 'User',
            'email' => 'user@example.com',
            'password' => Hash::make('admin123'),
        ]);
        $user->roles()->attach($user_role);
    }
}
