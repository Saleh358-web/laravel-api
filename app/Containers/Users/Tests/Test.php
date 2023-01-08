<?php

namespace  App\Containers\Users\Tests;

use Tests\TestDatabaseTrait;
use Tests\TestCase;
use Artisan;

class Test extends TestCase
{
    use TestDatabaseTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manageDatabase();
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_that_true_is_true()
    {
        $this->setUp();
        $this->assertTrue(true);
    }
}
