<?php
namespace app\admin\controller;
use think\Controller;
class BaseController extends Controller{
    protected $_redis = null;
    public function _initialize(){
        if(!session('?admin.user')){
            return $this->redirect(url('login/index'));
        }
        $config_redis = config('redis');
        $this-> _redis = new \Redis();
        $this-> _redis->connect($config_redis['host'], $config_redis['port'],$config_redis['timeout']);
        $this-> _redis->auth($config_redis['auth']);
    }
}