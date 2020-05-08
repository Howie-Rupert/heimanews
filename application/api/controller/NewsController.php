<?php

namespace app\api\controller;

use app\org\Page;
use think\Controller;

class NewsController extends Controller {
    private $table = null;

    public function _initialize() {
        $this->table = connMongodb()->news;
    }

    public function index() {
        $total = $this->table->count();
        $pagesize = 2;
        $page = new Page($total, $pagesize);
        $ret = $this->table->find([]);
        $data = $ret->toArray();
        return $data;
    }

    public function detail(string $id) {
        $_id = new \MongoDB\BSON\ObjectId($id);
        $ret = $this->table->findOne(['_id' => $_id]);
        return $ret;
    }
    public function view(string $id){
        $_id = new \MongoDB\BSON\ObjectId($id);
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $filter = ['_id'=> new \MongoDB\BSON\ObjectId($_id)];
        $query = new \MongoDB\Driver\Query($filter);
        $datas = $manager->executeQuery('php.news', $query);
        foreach($datas as $k){
            $title=$k->{'title'};
        }
        $filter = ['o_id'=> new \MongoDB\BSON\ObjectId($_id)];
        $query = new \MongoDB\Driver\Query($filter);
        $data = $manager->executeQuery('php.comment', $query);
        $key=0;
        $arr = [];
        foreach($data as $k => $v){
            $arr[$k]=$v;
            $key=1;
        }
        return $arr;
    }
    public function plun(){
        $data = input('post.');
        if($data == null){
            $res =[
                'code' => 201,
                'msg' => '未获取到数据'
            ];
            return $res;
        }
        if(!$data['id']){
            $res =[
                'code' => 201,
                'msg' => '未获取到数据'
            ];
            return $res;
        }
        if(!$data['username']){
            $res =[
                'code' => 201,
                'msg' => '未获取到数据'
            ];
            return $res;
        }
        if(!$data['pl']){
            $res =[
                'code' => 201,
                'msg' => '未获取到数据'
            ];
            return $res;
        }
        
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $filter = ['o_id'=>new \MongoDB\BSON\ObjectId($data['id']),'username'=>$data['username']];
        $query = new \MongoDB\Driver\Query($filter);
        $ress = $manager->executeQuery('php.comment', $query);
        $re =1;
        foreach($ress as $k){
            $re = 0;
        }
        
        if($re == 1){
            $bulk = new \MongoDB\Driver\BulkWrite;
            $date = date("Y-m-d");
            $bulk->insert(['o_id'=> new \MongoDB\BSON\ObjectId($data['id']),'date'=>$date,'username'=>$data['username'],'body'=>$data['pl']]);
            $manager->executeBulkWrite('php.comment', $bulk);
            $res = [
                'code' => 200,
                'msg' => '评论成功'
            ];
            return $res;
        }else{
            $res = [
                'code' => 202,
                'msg' => '无法再次评论'
            ];
            return $res;
        }
    }
    public function viewreply(string $id){
        $z=0;
        $_id = new \MongoDB\BSON\ObjectId($id);
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $filter = ['o_id'=>$_id];
        $query = new \MongoDB\Driver\Query($filter);
        $data = $manager->executeQuery('php.comment', $query);
        foreach($data as $k => $v){
            
            $o_id=(string)$v->{'_id'};
            $filter = ['o_id'=>$o_id];
            $query = new \MongoDB\Driver\Query($filter);
            $datas = $manager->executeQuery('php.reply', $query);
            $i=0;
            
            foreach($datas as $key =>$value){
                $array[$key] = $value;
                $i++;
                $z++;
                $arr[$z]=$array[$i-1];
            }
            
            
            
        }
        return $arr;
    }
    public function rep( ){
        $redis=  new \Redis();
        $redis->connect('139.196.225.118', 6379);
        $redis->auth('why');
        $user_id=$redis->get('user_id');
        $table = connMongodb()->success;
        $table = connMongodb()->user;
        $daa=$table->find();
        foreach($daa as $k ){
            if($k['_id']==$user_id){
                $username=$k['username'];
            };
        }
        $data = input('post.');
        // return $data;
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $bulk = new \MongoDB\Driver\BulkWrite;
        $date = date("Y-m-d");
        $bulk->insert(['o_id'=>$data['id'],'date'=>$date,'reply'=>$data['reply'],'rename'=>$data['rename'],'username'=>$data['username']]);
        $manager->executeBulkWrite('php.reply', $bulk);
        $result = [
            'code' => 200,
            'msg' => '评论成功'
        ];
        return $result;
    }
}
