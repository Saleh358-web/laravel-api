<?php

namespace App\Helpers\Response;

use App\Helpers\Response\ResponseJsonErrorReturn;
use Exception;

trait ResponseHelper
{
    /**
     * This function fills the response that should be sent to the user
     * then returns a response for it.
     * 
     * @param  int         $status  status of the error
     * @param  array       $data  data returned
     * @param  string|null $message response error message
     * @param  string $error   Exception message
     * 
     * @return \Illuminate\Http\Response
     */
    public function return_response(int $status, array $data, string $message = null, string $error = null)
    {
        if($status != 200) {
            return ResponseJsonErrorReturn::returnErrorResponse($status, $message, $error);
        }

        $data['status'] = 'success';

        if($message != null) {
            $data['message'] = $message;
        }

        return response()->json($data, 200);
    }

    /**
     * This function, receives an exception, 
     * checks if it is a general exception or a custom exception
     * builds a message and returns it
     * 
     * @param Exception $e
     * @return string $message
     */
    public function exception_message(Exception $e)
    {
        try {
            if($e->getMessage() != null && $e->getMessage() != '') {
                return $e->getMessage();
            }
            if($e->getMessage() == '') {
                // We have a normal exception
                return $e->error();
            }
        } catch (Exception $execption) {
            return $e->getMessage();
        }
        return $e->getMessage();
    }

    /**
     * This function, receives data that are paginated by laravel
     * Then it prettifies/customizes the data and returns it
     * 
     * @param $data
     * @return $data
     */
    public function metaData($data)
    {
        return [
            'current_page' => $data->current_page,
            'first_page_url' => $data->first_page_url,
            'from' => $data->from,
            'last_page' => $data->last_page,
            'last_page_url' => $data->last_page_url,
            'next_page_url' => $data->next_page_url,
            'path' => $data->path,
            'per_page' => $data->per_page,
            'prev_page_url' => $data->prev_page_url,
            'to' => $data->to,
            'total' => $data->total,
        ];
    }
}