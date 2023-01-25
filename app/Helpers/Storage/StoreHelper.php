<?php

namespace App\Helpers\Storage;

use App\Exceptions\Common\SaveFileFailedException;
use App\Exceptions\Common\GetFileFailedException;
use App\Exceptions\Common\NotFoundException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class StoreHelper
{
    /**
     * This function receives any file and subpath
     * It stores it in all available storages
     * and returns path if store was successful
     * or throws an exception if failed
     * 
     * @param $file
     * @param string $subPath
     * @return string $path | SaveFileFailedException
     */
    public static function storeFile($file, $subPath = '')
    {
        if(!$file) {
            throw new SaveFileFailedException(self::getFileType());
        }

        try {
            // Get File Extension
            $ext = $file->getClientOriginalExtension();
        } catch (Exception $e) {
            throw new SaveFileFailedException(self::getFileType());
        }
        
        try {
            $stored = false;

            $fileName = self::getFileName($file->getClientOriginalName());
    
            $destinationPath = 'storage/' . $subPath;
    
            if(env('STORE_IN_LOCAL')) {
                // We use move instead of store to preserve the name of the file
                $file->move($destinationPath, $fileName);
                $stored = true;
            }
    
            $path = $destinationPath . '/' . $fileName;
    
            if($stored) {
                return $path;
            }
        } catch (Exception $e) {
            throw new SaveFileFailedException(self::getFileType($ext));
        }

        throw new SaveFileFailedException(self::getFileType($ext));
    }

    /**
     * This function receives the sub path of a file 
     * and it returns its full link form local storage
     * 
     * @param string $path
     * @return string asset($path) | GetFileFailedException | NotFoundException
     */
    public static function getFileLink(string $path = '')
    {
        if(!$path || $path =='') {
            throw new GetFileFailedException(self::getFileType());
        }

        if(!File::exists($path)) {
            throw new NotFoundException(self::getFileType());
        }

        return asset($path);
    }

    /**
     * This function receives the sub path of a file 
     * and deletes it from local storage, and returns true if deleted
     * 
     * @param string $path
     * @return boolean | GetFileFailedException | NotFoundException
     */
    public static function deleteFile(string $path = '')
    {
        if(!$path || $path =='') {
            throw new GetFileFailedException(self::getFileType());
        }

        if(!File::exists($path)) {
            throw new NotFoundException(self::getFileType());
        }

        File::delete($path);
        return true;
    }

    /**
     * This function receives a file original name
     * then it returns a proper name for it to be saved
     * 
     * @param string $fileOriginalName
     * @return string $fileName
     */
    private static function getFileName(string $fileOriginalName = null)
    {
        $current_timestamp = Carbon::now()->timestamp;

        $fileName = $current_timestamp . '_';
        
        $fileName .= $fileOriginalName ? $fileOriginalName : Str::random(10);
        
        return $fileName;
    }

    /**
     * This function is used to return the proper type for
     * file. Value is sent to SaveFileFailedException in which it
     * uses the correct error message.
     * 
     * @param string $ext
     * @return string type
     */
    private static function getFileType($ext = '')
    {
        $type = 'File';

        switch ($ext) {
            case 'jpg': {
                $type = 'Image';
                break;
            }
            case 'jpeg': {
                $type = 'Image';
                break;
            }
            case 'png': {
                $type = 'Image';
                break;
            }
            default: {
                break;
            }
        }

        return $type;
    }
}