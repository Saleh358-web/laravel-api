<?php

namespace  App\Containers\Common\Tests;

use App\Exceptions\Common\ArgumentNullException;
use App\Exceptions\Common\CreateFailedException;
use App\Exceptions\Common\NotFoundException;

use App\Containers\Common\Helpers\DataHelper;
use App\Containers\Common\Models\DataType;
use App\Helpers\Tests\TestsFacilitator;
use Tests\TestCase;

class DataHelperTest extends TestCase
{
    use TestsFacilitator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    /**
     * Test successful formatValue.
     *
     * @return void
     */
    public function test_formatValue_successful()
    {
        // case json
        $value = ["random_key" => "random_value"];
        $typeId = DataType::where('slug', 'json')->first()->id; // json
        $stringifiedValue = DataHelper::stringifyValue($value, $typeId); // stringify value
        $result = DataHelper::formatValue($value, $typeId); // re-format value back
        $this->assertEquals($value, $result);

        // case boolean
        $value = true;
        $typeId = DataType::where('slug', 'bool')->first()->id; // boolean
        $stringifiedValue = DataHelper::stringifyValue($value, $typeId); // stringify value
        $result = DataHelper::formatValue($value, $typeId); // re-format value back
        $this->assertEquals($value, $result);

        // case number
        $value = rand(1, 100);
        $typeId = DataType::where('slug', 'number')->first()->id; // number
        $stringifiedValue = DataHelper::stringifyValue($value, $typeId); // stringify value
        $result = DataHelper::formatValue($value, $typeId); // re-format value back
        $this->assertEquals($value, $result);

         // case string
         $value = 'text';
         $typeId = DataType::where('slug', 'string')->first()->id; // string
         $stringifiedValue = DataHelper::stringifyValue($value, $typeId); // stringify value
         $result = DataHelper::formatValue($value, $typeId); // re-format value back
         $this->assertEquals($value, $result);
    }

    /**
     * Test fail formatValue on type.
     *
     * @return void
     */
    public function test_formatValue_type_fail()
    {
        $this->expectException(NotFoundException::class);

        $value = 'text';
        $typeId = 20;
        $result = DataHelper::stringifyValue($value, $typeId);

        $this->assertException($result, 'NotFoundException');
    }

    /**
     * Test fail formatValue on value.
     *
     * @return void
     */
    public function test_formatValue_value_fail()
    {
        $this->expectException(ArgumentNullException::class);

        $value = null;
        $typeId = 1;
        $result = DataHelper::stringifyValue($value, $typeId);

        $this->assertException($result, 'ArgumentNullException');
    }

    /**
     * Test successful stringifyValue.
     *
     * @return void
     */
    public function test_stringifyValue_successful()
    {
        // case json
        $value = ["random_key" => "random_value"];
        $typeId = DataType::where('slug', 'json')->first()->id; // json
        // json should be stringified
        $result = DataHelper::stringifyValue($value, $typeId);
        $this->assertEquals(json_encode($value), $result);
        $result = DataHelper::stringifyValue(json_encode($value), $typeId); // if value already stringified data should be returned the same
        $this->assertEquals(json_encode($value), $result);

        // case boolean
        $value = true;
        $typeId = DataType::where('slug', 'bool')->first()->id; // boolean
        $result = DataHelper::stringifyValue($value, $typeId);
        $this->assertEquals((string)$value, $result);
        $result = DataHelper::stringifyValue((string)$value, $typeId);
        $this->assertEquals((string)$value, $result);

        $value = rand(1, 100);
        $typeId = DataType::where('slug', 'number')->first()->id; // number
        $result = DataHelper::stringifyValue($value, $typeId);
        $this->assertEquals((string)$value, $result);
        $result = DataHelper::stringifyValue((string)$value, $typeId);
        $this->assertEquals((string)$value, $result);
    }

    /**
     * Test fail stringifyValue on type.
     *
     * @return void
     */
    public function test_stringifyValue_type_fail()
    {
        $this->expectException(NotFoundException::class);

        $value = 1;
        $typeId = 20;
        $result = DataHelper::stringifyValue($value, $typeId);

        $this->assertException($result, 'NotFoundException');
    }

    /**
     * Test fail stringifyValue on value.
     *
     * @return void
     */
    public function test_stringifyValue_value_fail()
    {
        $this->expectException(ArgumentNullException::class);

        $value = null;
        $typeId = 1;
        $result = DataHelper::stringifyValue($value, $typeId);

        $this->assertException($result, 'ArgumentNullException');
    }
}
