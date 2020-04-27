<?php

namespace app\admin\controller;

class NewsController extends BaseController
{
    public function index(){
        $data=[];
        $ids = $this -> _redis->zrange('news:zset:id',0,-1);
        foreach($ids as $id){
            $key = 'news:id:' . $id;
            $item = $this -> _redis->hgetall($key);
            $data[] = $item;
        }
        if(!$ids){
            $code = 0;
           return view('',compact('code'));
        }
        $code = 1;
         return view('',compact('data','code'));
    }
    public function create(){
        return view();
    }
    public function store(){
        $data = input();
        if(!$data['title']){
            return $this->error('请输入完整信息');
        }
        if(!$data['author']){
            return $this->error('请输入完整信息');
        }
        if(!$data['body']){
            return $this->error('请输入完整信息');
        }

        $idKey = 'news:id';
        $id = $this -> _redis->incr($idKey);

        $hkey = 'news:id:'.$id;
        $data['id'] = $id;

        $this -> _redis->hMSet($hkey,$data);
        $zkey = 'news:zset:id';
        $this -> _redis->zAdd($zkey,$id,$id);
        
        return $this->redirect(url('index'));
    }
    public function del(){
        $id = input('id');
        $hKey='news:id:' . $id;
        $this -> _redis->del($hKey);
        $zKey = 'news:zset:id';
        $this -> _redis->zrem($zKey,$id);
        return ['status' => 0,'msg' => '删除成功'];
    }
    public function delall(){
        $ids = $this -> _redis->zrange('news:zset:id',0,-1);
        foreach($ids as $id){
            $key = 'news:id:' . $id;
            $item = $this -> _redis->hgetall($key);
            $data[] = $item;
            $aid[] = $id;
        }
        $len = count($aid)-1; 
        for($i = 0;$i<=$len;$i++){
            $hKey='news:id:' . $aid[$i];
            $this -> _redis->del($hKey);
            $zKey = 'news:zset:id';
            $this -> _redis->zrem($zKey,$aid[$i]);
        }
        return ['status' => 0,'msg' => '删除成功'];
    }
    public function edit(){
        $data = input();
        $id = $data['id'];
        $key = 'news:id:' . $id;
        $item = $this -> _redis->hgetall($key);
        $olddata[] = $item;
        return view('',compact('olddata'));
    }
    public function update(){
        $data = input();


        $id = $data['id'];

        $hkey = 'news:id:'.$id;


        $this -> _redis->hMSet($hkey,$data);
        $zkey = 'news:zset:id';
        $this -> _redis->zAdd($zkey,$id,$id);
        
        return $this->redirect(url('news/index'));
    }
}

