<?php

namespace App\Helpers;

class ResponseBuilder
{
    public static function responseResult($http_code = '', $message = '', $data = '')
    {
        $response['http_code'] = $http_code;
        $response['message'] = $message;
        if (!empty($data)) {
            $response['data'] = $data;
        }
        return response()->json($response);
    }
}
