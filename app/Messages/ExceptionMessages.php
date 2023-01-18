<?php

namespace App\Messages;

trait ExceptionMessages
{
    public function messages()
    {
        return [
            'not_found' => 'is not found',
            'create' => 'create failed',
            'update' => 'update failed',
            'delete' => 'delete failed',
        ];
    }
}