<?php

namespace App\Containers\Users\Exceptions;

use App\Exceptions\ApplicationException;
use Illuminate\Http\Response;
use App\Containers\Users\Messages\Messages;

class SameOldPasswordException extends ApplicationException
{
    use Messages;

    public function status(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function help(): string
    {
        return trans($this->messages()['PROFILE']['OLD_PASSWORD_ERROR_EQUAL_NEW']);
    }

    public function error(): string
    {
        return trans($this->messages()['PROFILE']['OLD_PASSWORD_ERROR_EQUAL_NEW']);
    }
}