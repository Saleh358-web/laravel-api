<?php

namespace App\Exceptions\Common;

use App\Exceptions\ApplicationException;
use Illuminate\Http\Response;
use App\Messages\ExceptionMessages;

class NotFoundException extends ApplicationException
{
    use ExceptionMessages;

    protected $messages = array();
    protected $name = '';
    protected $message = '';

    public function __construct($name = '')
    {
        $this->name = $name;
        $this->messages = $this->messages();
        $this->message = $this->name == '' ? '' : $this->name . ' ';
        $this->message .= $this->messages['not_found'];
    }

    public function status(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function help(): string
    {
        return trans($this->message);
    }

    public function error(): string
    {
        return trans($this->message);
    }
}