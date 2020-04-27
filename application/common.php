<?php
function api(array $data, int $httpCode = 200) {
    return json([
        'status' => 0,
        'msg' => '成功',
        'data' => $data
    ], $httpCode, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PATCH, PUT, DELETE',
        'Access-Control-Allow-Headers' => 'Authorization, Content-Type, If-Match, If-Modified-Since, If-None-Match, If-Unmodified-Since, X-Requested-With'
    ]);
}