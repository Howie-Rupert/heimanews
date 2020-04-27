<?php

namespace app\admin\controller;

use think\Controller;

class LoginController extends Controller
{
    public function index(){
        return $this->fetch();
    }
    public function login()
    {
        $username = input('username');
        $password = input('password');
        $loginKey = 'user:' .$username;
        $redis = new \Redis();
        $redis->connect('139.196.225.118', 6379, 10);
        $redis->auth('why');
        // var_dump($redis->exists($loginKey));
        if(!$redis->exists($loginKey)){
            return $this->error('登录失败');
        }
        $pwd = $redis->get($loginKey);
        if ($pwd != $password){
            return $this->error('登录失败');
        }
        session('admin.user',$username);
        return $this->success('登陆成功',url('news/index'));
    }
}

