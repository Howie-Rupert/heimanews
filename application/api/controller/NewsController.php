<?php

namespace app\api\controller;

use think\Controller;

class NewsController extends Controller {
    // redis的对象
    protected $_redis = null;

    public function _initialize() {
        // 读取配置文件中的关于redis配置
        $config_redis = config('redis');
        // 调用redis
        $this->_redis = new \Redis();
        $this->_redis->connect($config_redis['host'], $config_redis['port'], $config_redis['timeout']);
        $this->_redis->auth($config_redis['auth']);
    }

    public function index() {
        // 显示的数据源
        $data = [];
        // 得到zset中的所有的id数据
        $ids = $this->_redis->zrange('news:zset:id', 0, -1);
        // 通过id可以得到每条id对应的具体的数据
        foreach ($ids as $id) {
            // hash的key
            $key = 'news:id:' . $id;
            $item = $this->_redis->hmget($key,['id','title']);
            $data[] = $item;
        }
        return api($data);
    }

    public function detail(int $id) {
        // hash的key
        $key = 'news:id:' . $id;
        $data = $this->_redis->hgetall($key);

        return api($data);
    }
}
