<?php

if (!function_exists('AuthApi')) {
    function AuthApi()
    {
        return auth()->guard('api');
    }
}

if (!function_exists('res_data')) {
    function res_data($data, $message = null, $status = 200)
    {
        $message = $message ?? __('main.success');
        return response([
            'message' => $message,
            'result' => !empty($data) ? $data : null,
            'statusCode' => $status,
            'status' => in_array($status, [200, 201, 203])

        ], $status);
    }
}
