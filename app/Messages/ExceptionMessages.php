<?php

namespace App\Messages;

trait ExceptionMessages
{
    public function messages()
    {
        return [
            'not_found' => 'is not found',
            'not_allowed' => 'You are not authorized to update ',
            'create' => 'create failed',
            'update' => 'update failed',
            'delete' => 'delete failed',
        ];
    }
}