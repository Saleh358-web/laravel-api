<?php

namespace App\Helpers\Storage;

use Illuminate\Support\Facades\Storage;
use App\Helpers\Storage\StoreHelper;

class LocalStore
{
    const DIRECTORY = 'local';
    /**
     * This function receives a string representing
     * the file name in which we are storing data in the local storage.
     * If $file is an empty string we return the default name
     * else we re-return the file name in
     * 
     * @param string $file
     * @return string $defaultName | $file
     */
    public static function getFileName(string $file = '')
    {
        $defaultName = self::DIRECTORY . '/storage.txt';

        if($file == '') {
            return $defaultName;
        }

        return self::DIRECTORY . '/' . $file . '.txt';
    }

    public static function get(string $key, string $file = '')
    {
        $file = self::getFileName($file);

        $data = self::getJsonData($file);

        if($data && isset($data[$key])) {
            return $data[$key];
        }

        return null;
    }

    public static function set(string $key, $value, string $file = '')
    {
        $fileName = self::getFileName($file);
        $json = self::getJsonData($fileName);

        if($json == null) {
            $json = array();
        }

        $json[$key] = $value;

        self::save($json, $file);
    }

    private static function save($data, string $file = '')
    {
        $file = self::getFileName($file);

        $exists = Storage::disk('local')->exists($file);

        if($exists) {
            Storage::disk('local')->delete($file);
        }
        
        $json = json_encode($data);

        Storage::disk('local')->put($file, $json);
    }

    private static function getJsonData(string $fileName)
    {
        $exists = Storage::disk('local')->exists($fileName);

        if(!$exists) {
            return null;
        }

        $file = Storage::disk('local')->get($fileName);

        $json = json_decode($file, true);

        return $json;
    }
}