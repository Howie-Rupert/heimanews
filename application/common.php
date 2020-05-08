<?php
use MongoDB\Client;
function connMongodb(){
    $client = new \MongoDB\Client('mongodb://phpuser:phppwd@139.196.225.118:27017/php');
    return $client->php;
}
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