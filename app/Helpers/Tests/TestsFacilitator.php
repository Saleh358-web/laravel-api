<?php

namespace App\Helpers\Tests;

use App\Models\User;
use App\Containers\Users\Helpers\UserHelper;
use Illuminate\Support\Str;

/*
* This helper facilitates tests,
* for example login in user, creates and logs in user, etc...
*
* These functions will be used only by phpunit tests
*/
trait TestsFacilitator
{
    /**
     * This function receives 
     *  - credentials that are email and password
     *  - or user raw data this function constructs the credentials
     * then logins user and return response content
     * 
     * @param $credentials
     * @param $userRawData
     * @return $content of response
     */
    public function login($credentials, $userRawData = null)
    {
        if($userRawData != null || $credentials == null) {
            $credentials = [
                'email' => $userRawData['email'],
                'password' => $userRawData['password']
            ];
        }

        $response = $this->json('POST', '/api/v1/login', $credentials, ['Accept' => 'application/json']);

        $response->assertStatus(200);

        $content = json_decode($response->getContent());

        return $content;
    }

    /**
     * This function creates random user in the database
     * and returns him with his raw data
     * 
     */
    public function createUser()
    {
        $userRawData = [
            'first_name' => Str::random(5),
            'last_name' => Str::random(5),
            'email' => Str::random(5) . '@' . Str::random(5) . '.com',
            'password' => Str::random(8) . 'A@1' // password should have at lease 1 capital letter, 1 character and 1 number
        ];

        return [
            'userRawData' => $userRawData,
            'user' => UserHelper::create($userRawData)
        ];
    }
}