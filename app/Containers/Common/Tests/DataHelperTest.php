<?php

namespace  App\Containers\Common\Tests;

use App\Exceptions\Common\ArgumentNullException;
use App\Exceptions\Common\CreateFailedException;
use App\Exceptions\Common\UpdateFailedException;
use App\Exceptions\Common\NotFoundException;

use App\Containers\Common\Helpers\DataHelper;
use App\Containers\Common\Models\DataType;
use App\Containers\Common\Models\Data;

use App\Helpers\Tests\TestsFacilitator;
use Tests\TestCase;

use Illuminate\Support\Str;

class DataHelperTest extends TestCase
{
    use TestsFacilitator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    private function getDataArray(): array
    {
        $value = [Str::random(5) => Str::random(5)];
        $typeId = DataType::where('slug', 'json')->first()->id; // json
        $data = [
            'key' => Str::random(5),
            'value' => $value,
            'type_id' => $typeId,
            'description' => Str::random(10)
        ];
        return $data;
    }

    private function createNewData()
    {
        $data = $this->getDataArray();
        $newData = DataHelper::create($data);
        return $newData;
    }

    /**
     * Test successful key.
     *
     * @return void
     */
    public function test_key_successful()
    {
        $newData = $this->createNewData();
        $result = DataHelper::key($newData->key);
        $this->assertEquals(Data::where('key', $newData->key)->first(), $result);
    }

    /**
     * Test fail key.
     *
     * @return void
     */
    public function test_key_fail()
    {
        $this->expectException(NotFoundException::class);

        $result = DataHelper::key(Str::random(7));

        $this->assertException($result, 'NotFoundException');
    }

    /**
     * Test successful id.
     *
     * @return void
     */
    public function test_id_successful()
    {
        $newData = $this->createNewData();
        $result = DataHelper::id($newData->id);
        $this->assertEquals(Data::find($newData->id), $result);
    }

    /**
     * Test fail id.
     *
     * @return void
     */
    public function test_id_fail()
    {
        $this->expectException(NotFoundException::class);

        $result = DataHelper::id(4512154214521);

        $this->assertException($result, 'NotFoundException');
    }

    /**
     * Test successful create.
     *
     * @return void
     */
    public function test_create_successful()
    {
        $value = ['random_key' => 'random_value'];
        $typeId = DataType::where('slug', 'json')->first()->id; // json
        $data = [
            'key' => 'random_key',
            'value' => $value,
            'type_id' => $typeId,
            'description' => 'description'
        ];

        $result = DataHelper::create($data);
        $newData = Data::orderBy('id', 'desc')->first();
        $this->assertEquals($result, $newData);
    }

    /**
     * Test fail create on duplicate key.
     *
     * @return void
     */
    public function test_create_key_fail()
    {
        $newData = $this->createNewData();
        $data = [
            'key' => $newData->key, // same key as before should throw exception
            'value' => $newData->value,
            'type_id' => $newData->type_id,
            'description' => $newData->description
        ];
        $this->expectException(CreateFailedException::class);
        $result = DataHelper::create($data);
        $this->assertException($result, 'CreateFailedException');
    }

    /**
     * Test fail create on value.
     *
     * @return void
     */
    public function test_create_value_fail()
    {
        $this->expectException(CreateFailedException::class);

        $value = null;
        $typeId = DataType::where('slug', 'json')->first()->id; // json
        $data = [
            'key' => 'random_key',
            'value' => $value,
            'type_id' => $typeId,
            'description' => 'description'
        ];

        $result = DataHelper::create($data);

        $this->assertException($result, 'CreateFailedException');
    }

    /**
     * Test fail create on type.
     *
     * @return void
     */
    public function test_create_type_fail()
    {
        $this->expectException(CreateFailedException::class);

        $value = ['random_key' => 'random_value'];
        $typeId = 441;
        $data = [
            'key' => 'random_key',
            'value' => $value,
            'type_id' => $typeId,
            'description' => 'description'
        ];

        $result = DataHelper::create($data);

        $this->assertException($result, 'CreateFailedException');
    }

    /**
     * Test successful update.
     *
     * @return void
     */
    public function test_update_successful()
    {
        $data = $this->createNewData();
        $updatedArray = $this->getDataArray();

        $result = DataHelper::update($data, $updatedArray);
        $this->assertEquals($result, DataHelper::id($data->id));
    }

    /**
     * Test fail update on duplicate key.
     *
     * @return void
     */
    public function test_update_key_fail()
    {
        $oldData = $this->createNewData();

        $data = $this->createNewData();
        $updatedArray = $this->getDataArray();
        
        $updatedArray['key'] = $oldData->key;

        $this->expectException(UpdateFailedException::class);
        $result = DataHelper::update($data, $updatedArray);
        $this->assertException($result, 'UpdateFailedException');
    }

    /**
     * Test fail update on invalid type.
     *
     * @return void
     */
    public function test_update_type_fail()
    {
        $data = $this->createNewData();
        $updatedArray = $this->getDataArray();
        
        $updatedArray['type_id'] = 84653486532;

        $this->expectException(UpdateFailedException::class);
        $result = DataHelper::update($data, $updatedArray);
        $this->assertException($result, 'UpdateFailedException');
    }

    /**
     * Test fail update on invalid value.
     *
     * @return void
     */
    public function test_update_value_fail()
    {
        $data = $this->createNewData();
        $updatedArray = $this->getDataArray();
        
        $updatedArray['value'] = null;

        $this->expectException(UpdateFailedException::class);
        $result = DataHelper::update($data, $updatedArray);
        $this->assertException($result, 'UpdateFailedException');
    }

    /**
     * Test successful getValue.
     *
     * @return void
     */
    public function test_getValue_successful()
    {
        // case json
        $value = ['random_key' => 'random_value'];
        $typeId = DataType::where('slug', 'json')->first()->id; // json
        $data = $this->formatData($value, $typeId);
        $result = DataHelper::getValue($data);
        $this->assertEquals($value, $result);

        // case boolean
        $value = true;
        $typeId = DataType::where('slug', 'bool')->first()->id; // boolean
        $data = $this->formatData($value, $typeId);
        $result = DataHelper::getValue($data);
        $this->assertEquals($value, $result);

        // case number
        $value = 5632;
        $typeId = DataType::where('slug', 'number')->first()->id; // number
        $data = $this->formatData($value, $typeId);
        $result = DataHelper::getValue($data);
        $this->assertEquals($value, $result);
    }

    private function formatData($value, $typeId)
    {
        $data = new Data();
        $data->value = $value;
        $data->type_id = $typeId;
        $data->value = DataHelper::stringifyValue($data->value, $typeId);
        return $data;
    }
    
    /**
     * Test fail getValue on type.
     *
     * @return void
     */
    public function test_getValue_type_fail()
    {
        $this->expectException(NotFoundException::class);

        $value = ['random_key' => 'random_value'];
        $typeId = 4512;
        $data = $this->formatData($value, $typeId);

        $this->assertException($result, 'NotFoundException');
    }

    /**
     * Test fail getValue on value.
     *
     * @return void
     */
    public function test_getValue_value_fail()
    {
        $this->expectException(ArgumentNullException::class);

        $value = null;
        $typeId = 1;
        $data = $this->formatData($value, $typeId);

        $this->assertException($result, 'ArgumentNullException');
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
