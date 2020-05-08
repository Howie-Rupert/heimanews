<?php

namespace app\admin\controller;
use think\Controller;
use app\org\Page;
use MongoDB\Client;
use think\Captcha;
class UserController extends Controller {
    public function index() {
        $redis=  new \Redis();
        $redis->connect('139.196.225.118', 6379);
        $redis->auth('why');
        $user_id=$redis->get('user_id');
        $pic_name=$redis->get('pic_name');
        $table = connMongodb()->success;
        $data=$table->find();
        $arr = array();
        $total = $table->count();
        $pagesize = 2;
        $page = new Page($total,$pagesize);
        $ret = $table ->find([],[
            'projection' => ['body'=>0],
            'skip'=>$page->offset,
            'limit'=>$pagesize,
            'sort'=>['click'=>-1]
        ]);
        $data = $ret-> toArray();   
        $fpage = $page->fpage();
        
        foreach($data as $k => $v){
                $arr[$k] = $v;
            }
        $table = connMongodb()->user;
        $daa=$table->find();
        foreach($daa as $k ){
            if($k['_id']==$user_id){
                $username=$k['username'];
            };
        }

        return view('',compact('arr','pic_name','username','fpage'));
    }
    public function edit(){
        return view('');
    }
    public function pic(){
        $file = request()->file('file');
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                $redis=  new \Redis();
                $redis->connect('139.196.225.118', 6379);
                $redis->auth('why');
                $pathname = $info->getSaveName();
                $ret=$redis->set('pic_name',$pathname);
                if(!$ret){
                    return $this->error('图片保存错误');
                }
                return $this->redirect(url('user/index'));
            }else{
                echo $file->getError();
            }
        }
    }
    public function passedit(){
        $redis=  new \Redis();
        $redis->connect('139.196.225.118', 6379);
        $redis->auth('why');
        $user_id=$redis->get('user_id');
        $table = connMongodb()->user;
        $daa=$table->find();
        foreach($daa as $k ){
            if($k['_id']==$user_id){
                $username=$k['username'];
            };
        }
        return view('',compact('username'));
    }
    public function pass(){
        $passwd = $_POST['password'];  
        $redis=  new \Redis();
        $redis->connect('139.196.225.118', 6379);
        $redis->auth('why');
        $user_id=$redis->get('user_id');
        $id=(int)$user_id;
        if(!$passwd){
            $this->error('请修改密码');
        }
        if(mb_strlen($passwd) < 6){
            $this->error('密码长度不能少于6位');
        }
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['_id' => $id],
            ['$set' => ['password' => $passwd]],
            ['multi' => true, 'upsert' => false]
        );
        $res=$manager->executeBulkWrite('php.user', $bulk);
        $this->success('修改成功，请重新登录','login/index');
    }
}
