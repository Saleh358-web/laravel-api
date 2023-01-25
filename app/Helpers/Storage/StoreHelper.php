<?php

namespace App\Helpers\Storage;

use Illuminate\Support\Str;
// use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class StoreHelper
{
    public static function storeFile($file, $subPath)
    {
        $stored = false;

        $current_timestamp = Carbon::now()->timestamp;

        // Get File Extension
        $ext = $file->getClientOriginalExtension();

        $fileName = $file->getClientOriginalName() ? $file->getClientOriginalName() : Str::random(10);
        // $fileName .- '_.' . $ext;

        //Move Uploaded File
        $destinationPath = 'storage/uploads/' . $subPath;

        if(env('STORE_IN_LOCAL')) {
            $file->move($destinationPath, $fileName);
            $stored = true;
        }

        $path = $destinationPath . '/' . $fileName;

        if($stored) {
            return $path;
        }
    }
}