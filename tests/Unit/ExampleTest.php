<?php

namespace Tests\Unit;

use Tests\TestDatabaseTrait;
use Tests\TestCase;;

class ExampleTest extends TestCase
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
        $this->assertTrue(true);
    }
}
