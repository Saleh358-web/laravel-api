<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Helpers\Database\PermissionsHelper;

class PermissionsTableSeeder extends Seeder
{
    use PermissionsHelper;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->addPermissions();
    }
}
