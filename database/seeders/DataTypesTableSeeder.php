<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Containers\Common\Models\DataType;

class DataTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
                'name' => 'Boolean',
                'slug' => 'bool',
                'description' => 'This type represents any true or false value.'
            ],
            [
                'name' => 'Integer',
                'slug' => 'int',
                'description' => 'This type represents any integer.'
            ],
            [
                'name' => 'Number',
                'slug' => 'number',
                'description' => 'This type represents any number.'
            ],
            [
                'name' => 'Text',
                'slug' => 'string',
                'description' => 'This type represents any text data.'
            ],
            [
                'name' => 'Java Script Object Notation',
                'slug' => 'json',
                'description' => 'This type represents any JSON type data.'
            ]
        ];
        foreach($types as $type) {
            DataType::create($type);
        }
    }
}
