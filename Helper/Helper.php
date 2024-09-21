<?php
namespace Helper;

class Helper
{
    public static function error($code)
    {
        $codes = [
            400 => 'Bad Request',
            404 => 'Not Found',
        ];
        http_response_code($code);
        echo json_encode(['error' => $codes[$code]]);
    }

    public static function clientError($message)
    {
        http_response_code(400);

        echo json_encode(['error' => $message, 'status' => 'ERR']);
    }

    public static function success($message)
    {
        http_response_code(200);

        echo json_encode(['message' => $message, 'status' => 'OK']);
    }
}