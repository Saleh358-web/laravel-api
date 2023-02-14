<?php

namespace App\Containers\Common\Helpers;

use App\Exceptions\Common\ArgumentNullException;
use App\Exceptions\Common\CreateFailedException;
use App\Exceptions\Common\UpdateFailedException;
use App\Exceptions\Common\DeleteFailedException;
use App\Exceptions\Common\NotFoundException;
use Exception;

use App\Containers\Common\Messages\Messages;

use App\Containers\Common\Models\DataType;
use App\Containers\Common\Models\Data;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataHelper
{
    use Messages;

    public static function getMessages()
    {
        $dataHelper = new DataHelper();
        $messages = $dataHelper->messages();
        return $messages;
    }

    /**
     * get a data object by id
     * 
     * @param  int $id
     * @return Data | NotFoundException
     */
    public static function id(int $id)
    {
        $data = Data::find($id);
        if(!$data) {
            throw new NotFoundException('Data');
        }
        return $data;
    }

    /**
     * get a data object by key
     * 
     * @param  string $key
     * @return Data | NotFoundException
     */
    public static function key(string $key)
    {
        $data = Data::where('key', $key)->first();;
        if(!$data) {
            throw new NotFoundException('Data');
        }
        return $data;
    }

    /**
     * create a new data object
     * 
     * @param  array $data
     * @return Data | CreateFailedException
     */
    public static function create(array $data)
    {
        $messages = self::getMessages();

        DB::beginTransaction();
        try {
            $data['value'] = self::stringifyValue($data['value'], $data['type_id']);
            $newData = Data::create($data);
            DB::commit();

            Log::info('New data created successfully');
            return self::id($newData->id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Create data failed - DataHelper::create');
            throw new  CreateFailedException($messages['DATA']['EXCEPTION']);
        }

        Log::error('Create data failed - DataHelper::create');
        throw new  CreateFailedException($messages['DATA']['EXCEPTION']);
    }

    /**
     * update a data object
     * 
     * @param Data $updateData
     * @param  array $data
     * @return Data | UpdateFailedException
     */
    public static function update(Data $updateData, array $data)
    {
        $messages = self::getMessages();

        DB::beginTransaction();
        try {
            $data['value'] = self::stringifyValue($data['value'], $data['type_id']);

            $updateData->key = $data['key'];
            $updateData->type_id = $data['type_id'];
            $updateData->value = $data['value'];
            $updateData->description = $data['description'];
            $updateData->save();

            DB::commit();

            Log::info('Data updated successfully');
            return self::id($updateData->id);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Update data failed - DataHelper::update');
            throw new  UpdateFailedException($messages['DATA']['EXCEPTION']);
        }

        Log::error('Update data failed - DataHelper::update');
        throw new  UpdateFailedException($messages['DATA']['EXCEPTION']);
    }

    /**
     * delete a data object by id
     * 
     * @param  int $data
     * @return Boolean | DeleteFailedException
     */
    public static function delete(int $id)
    {
        $messages = self::getMessages();

        DB::beginTransaction();
        try {
            $data = self::id($id);
            $data->delete();
            DB::commit();

            Log::info('Data deleted successfully');
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Delete data failed - DataHelper::delete');
            throw new  DeleteFailedException($messages['DATA']['EXCEPTION']);
        }

        Log::error('Delete data failed - DataHelper::delete');
        throw new  DeleteFailedException($messages['DATA']['EXCEPTION']);
    }

    /**
     * delete a data object by key
     * 
     * @param  string $key
     * @return Boolean | DeleteFailedException
     */
    public static function deleteByKey(string $key)
    {
        $messages = self::getMessages();

        DB::beginTransaction();
        try {
            $data = self::key($key);
            $data->delete();
            DB::commit();

            Log::info('Data deleted successfully');
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Delete data failed - DataHelper::deleteByKey');
            throw new  DeleteFailedException($messages['DATA']['EXCEPTION']);
        }

        Log::error('Delete data failed - DataHelper::deleteByKey');
        throw new  DeleteFailedException($messages['DATA']['EXCEPTION']);
    }

    /**
     * Formats the value of the data to the manageable form
     * so it converts string values to corresponding php result
     * and returns it
     * 
     * @param  Data $data
     * @return $value
     */
    public static function getValue(Data $data)
    {
        try {
            $output = self::formatValue($data->value, $data->type_id);
            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * This function formats the value to be the same as
     * its type
     * 
     * so a string value with boolean type will be converted to be boolean
     * etc, ...
     * 
     * @param $value
     * @return $output
     */
    public static function formatValue($value, $typeId)
    {
        $output = null;
        $type = DataType::find($typeId);
        try {
            if(!$type) {
                throw new NotFoundException('Data Type');
            }
            if(!$value) {
                throw new ArgumentNullException('Value');
            }
            $currentType = gettype($value);

            switch($type->slug) {
                case 'json': {
                    $currentType == 'string' ? $output = json_decode($value, true) : $output = $value;
                    break;
                }
                case 'bool': {
                    $currentType != 'boolean' ? $output = (bool)$value : $output = $value;
                    break;
                }
                case 'int': {
                    $currentType != 'integer' ? $output = (int)$value : $output = $value;
                    break;
                }
                case 'number': {
                    $currentType != 'double' ? $output = (double)$value : $output = $value;
                    break;
                }
                default: {
                    $output = $value;
                    break;
                }
            }

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Gets the value and the type of the value
     * converts it to string and returns it back
     * 
     * @param $value
     * @param $typeId
     * @return string $output
     */
    public static function stringifyValue($value, $typeId)
    {
        $output = null;
        $type = DataType::find($typeId);
        try {
            if(!$type) {
                throw new NotFoundException('Data Type');
            }
            if(!$value) {
                throw new ArgumentNullException('Value');
            }
            switch($type->slug) {
                case 'json': {
                    if(gettype($value) == 'string') {
                        $output = $value;
                    } else {
                        $output = json_encode($value, true);
                    }
                    break;
                }
                default: {
                    $output = (string)$value;
                    break;
                }
            }

            return $output;
        } catch (Exception $e) {
            throw $e;
        }
    }
}