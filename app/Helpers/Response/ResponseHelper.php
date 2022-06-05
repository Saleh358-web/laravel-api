<?php

namespace App\Helpers\Response;

use App\Helpers\ResponseJsonErrorReturn;

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
}