<?php

namespace App\Containers\Common\Helpers;

use App\Exceptions\Common\CreateFailedException;
use App\Containers\Users\Messages\Messages;
use App\Containers\Common\Models\DataType;
use App\Containers\Common\Models\Data;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

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
            $newData = Data::create($data);
            DB::commit();

            Log::info('New data created successfully');
            return $newData;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Create data failed - DataHelper::create');
            throw new  CreateFailedException($messages['DATA']['EXCEPTION']);
        }
        
        Log::error('Create data failed - DataHelper::create');
        throw new  CreateFailedException($messages['DATA']['EXCEPTION']);
    }
}