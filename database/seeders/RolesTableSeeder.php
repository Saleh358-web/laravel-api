<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Has full access to everything'
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Has access to permissions that the super admin assigns to him'
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Has access to permissions that the super admin or admin assigns to him'
            ]
        ];
        foreach($roles as $role) {
            Role::create($role);
        }
    }
}
