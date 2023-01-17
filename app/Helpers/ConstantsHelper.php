<?php

namespace App\Helpers;

class ConstantsHelper
{
    public static function getPagination(int $pagination = null)
    {
        if($pagination == null) {
            $pagination = env('APP_PAGINATION') ? env('APP_PAGINATION') : 50;
        }
        
        return $pagination;
    }
}