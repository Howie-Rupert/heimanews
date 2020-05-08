<?php

namespace app\admin\controller;
use think\Controller;
use MongoDB\Client;
use think\Captcha;

class LoginController extends Controller {
    public function index() {
        ini_set("session.save_handler", "memcache"); 
        ini_set("session.save_path", "tcp://127.0.0.1:11211"); 
        session_start();
        return view();
    }
    public function login() {
        $error_count_key = 'error_count_key:0';
        $redis=  new \Redis();
        $redis->connect('139.196.225.118', 6379);
        $redis->auth('why');
        $table = connMongodb()->user;
        $code = input('captcha');
        if (!captcha_check($code)){
            return $this->error('验证码错误');
        }
        session_destroy();
        ini_set("session.save_handler", "memcache"); 
        ini_set("session.save_path", "tcp://127.0.0.1:11211"); 
        session_start();
        $name = $_POST['username'];
        $passwd = $_POST['password'];    
        $user=[
            'username' => $name,
            'password' => $passwd
            ];
        $ret = $table->findOne($user);
        if($redis->get($error_count_key) >=10){
            $redis->expire($error_count_key,10000);
            return $this->error('请您24小时后再登入吧');
        }
        if($ret){
            $user_id=$ret['_id'];
            session('admin.user',$ret);
            $_SESSION['user']=$name;
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
            $redis->set('user_id',$user_id);
            return $this->redirect(url('news/index'));
        }else{
            if($redis->exists($error_count_key)){
                $redis->Incr ($error_count_key);
            }else{
                $redis->set($error_count_key,1);
            }
            return $this->error('账户或密码错误');
        }

    }
    
}
