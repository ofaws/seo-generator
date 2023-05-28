<?php

namespace App\Exceptions;

use Exception;

class ErrorResponseException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request)
    {
        if ($request->is('api/*')) {
            return response()->json([
                'message' => $this->message
            ], $this->code != 0 ? $this->code : 500);
        }
    }

    /**
     * Get the default context variables for logging.
     *
     * @return array
     */
    public function context()
    {
        return ['from' => url()->previous()];
    }
}
