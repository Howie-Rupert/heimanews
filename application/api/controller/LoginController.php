<?php

namespace app\api\controller;

use app\org\Page;
use think\Controller;

class LoginController extends Controller {
    private $table = null;

    public function _initialize() {
        $this->table = connMongodb()->user;
    }

    public function index( ) {
        $data = input('post.');
        $name = $data['username'];
        $passwd = $data['password'];
        $user=[
            'username' => $name,
            'password' => $passwd
            ];
        $ret = $this->table->findOne($user);
        if($ret){
            $memcache = memcache_connect('127.0.0.1', 11211);
            $table = connMongodb()->success;
            $ip =  $_SERVER["REMOTE_ADDR"];
            $date = date("Y-m-d");
            $suc=[
                'ip' => $ip,
                'date' => $date,
                'username'=>$name
            ];
            $table->insertOne($suc);
            $res = [
                'code' => 200,
                'username' => $name,
                'msg' => '登陆成功'
            ];
            return $res; 
        }else{
            $res = [
                'code' => 201,
                'msg' => '登陆失败'
            ];
            return $res;
        }
    }
    public function logout()
    {
    	// 1. 清除会话信息
    	session(null);
    	// 2. 跳转到登录界面
        $res = [
            'code' => 202,
            'msg' => '登出'
        ];
        return $res;
    }
}
