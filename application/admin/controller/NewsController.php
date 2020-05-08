<?php
namespace app\admin\controller;
use app\org\Page;
use think\Controller;
class NewsController extends Controller {
    private $table = null;
    public function _initialize(){
        $this ->table= connMongodb()->news;
    }
    public function index(){
        $da=input('post.');
        if($da != null){
            $keyword=$da['keyword'];
            if($keyword != null){
                $total = $this->table->count();
                $pagesize = 2;
                $page = new Page($total,$pagesize);
                $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
                $filter = [];
                $filter['title'] = [
                    '$regex' => '.*'. $keyword . '.*',
                    '$options' => 'i'
                ];
                $options       = [
                    'projection' => ['body'=>0],
                    'skip'=>$page->offset,
                    'limit'=>$pagesize
                ];
                $query = new \MongoDB\Driver\Query($filter, $options);
                $res = $manager->executeQuery('php.news', $query);
                foreach($res as $k){
                        $id = $k->{'_id'};
                }
                $ret = $this -> table ->find(['_id' => new \MongoDB\BSON\ObjectId($id)],[
                    'projection' => ['body'=>0],
                    'skip'=>$page->offset,
                    'limit'=>$pagesize
                ]);
                $data = $ret-> toArray();   
                $fpage = $page->fpage();
                $keywork = null;
                return view('',compact('data','fpage')); 
            } 
        }
        $total = $this->table->count();
        $pagesize = 2;
        $page = new Page($total,$pagesize);
        $ret = $this -> table ->find([],[
            'projection' => ['body'=>0],
            'skip'=>$page->offset,
            'limit'=>$pagesize
        ]);
        $data = $ret-> toArray();   
        $fpage = $page->fpage();
        // dump($ret);die;
        return view('',compact('data','fpage'));
    }
    public function create(){
        return view('');
    }
    public function store(){
        $table= connMongodb()->news;
        $data = input('post.');
        $data['ctime'] = time();
        $data['click'] = 100;
        if(!$data['title']){
            return $this->error('请填写完整');
        }
        if(!$data['body']){
            return $this->error('请填写完整');
        }
        if(!$data['desn']){
            return $this->error('请填写完整');
        }
        $table->insertOne($data);
        return $this->success('新闻添加成功','news/index');
    }
    public function del(string $_id){
        $this->table->deleteMany(['_id'=>new \MongoDB\BSON\ObjectId($_id)]);
        return $this->success('删除成功','/admin/news/index');
    }
    public function codel(string $_id){
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->delete(['_id' => new \MongoDB\BSON\ObjectId($_id)], ['limit' => 0]); 
        $manager->executeBulkWrite('php.comment', $bulk);
        return $this->success('删除成功','admin/news/comment');
    }
    public function redel(string $_id){
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->delete(['_id' => new \MongoDB\BSON\ObjectId($_id)], ['limit' => 0]); 
        $manager->executeBulkWrite('php.reply', $bulk);
        return $this->success('删除成功','admin/news/index');
    }
    public function delete(){
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $data = input('post.');
        $bulk = new \MongoDB\Driver\BulkWrite;
        $id=$data['id'];
        foreach($id as $k){
            $bulk->delete(['_id' => new \MongoDB\BSON\ObjectId($k)], ['limit' => 0]);
            $manager->executeBulkWrite('php.news', $bulk);
        }
        return $this->success('删除成功','/admin/news/index');
    }
    public function edit(string $_id){
        
        $ret = $this->table->find(['_id'=>new \MongoDB\BSON\ObjectId($_id)]);
        foreach($ret as $k){
            $data = $k;
        }
        return view('',compact('data'));
    }
    public function save(){
        $data = input('post.');
        $data['ctime'] = time();
        $data['click'] = 100;
        if(!$data['title']){
            return $this->error('请填写完整');
        }
        if(!$data['body']){
            return $this->error('请填写完整');
        }
        if(!$data['desn']){
            return $this->error('请填写完整');
        }
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['_id' => new \MongoDB\BSON\ObjectId($data['id'])],
            ['$set' => ['title' => $data['title'], 'desn' => $data['desn'],'body' => $data['body'],
            'ctime' => $data['ctime'],'click' => $data['click'],]],
            ['multi' => true, 'upsert' => false]
        );
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $manager->executeBulkWrite('php.news', $bulk);
        return $this->success('修改成功','news/index');
    }
    public function list(string $_id){
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $filter = ['_id'=> new \MongoDB\BSON\ObjectId($_id)];
        $query = new \MongoDB\Driver\Query($filter);
        $data = $manager->executeQuery('php.news', $query);
        foreach($data as $k){
            $cli = $k->{'click'};
            $ret = [
                'title' => $k->{'title'},
                'desn' => $k->{'desn'},
                'body' => $k->{'body'}
            ];
        }
        $cli+=1;
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->update(
            ['_id' => new \MongoDB\BSON\ObjectId($_id)],
            ['$set' => ['click' => $cli]]
        );
        $manager->executeBulkWrite('php.news', $bulk);
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
        return view('',compact('ret','_id','username'));
    }
    public function paixu(){
        $total = $this->table->count();
        $pagesize = 2;
        $page = new Page($total,$pagesize);
        $ret = $this -> table ->find([],[
            'projection' => ['body'=>0],
            'skip'=>$page->offset,
            'limit'=>$pagesize,
            'sort'=>['click'=>-1]
        ]);
        $data = $ret-> toArray();   
        $fpage = $page->fpage();
        return view('index',compact('data','fpage'));
    }
    public function comment(){
        $data=$this->table->find();
        $arr=array();
        foreach($data as $k => $v){
            $arr[$k]=$v;
        }
        return view('',compact('arr'));
    }
    public function plun(){
        $data = input('post.');
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $bulk = new \MongoDB\Driver\BulkWrite;
        $date = date("Y-m-d");
        $bulk->insert(['o_id'=> new \MongoDB\BSON\ObjectId($data['id']),'date'=>$date,'username'=>$data['username'],'body'=>$data['pl']]);
        $manager->executeBulkWrite('php.comment', $bulk);
        return $this->success('评论成功','news/index');
    }
    public function view(string $_id){
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
        return view('',compact('key','arr','title'));
    }
    public function reply(string $_id){
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $id = $_id;
        $filter = ['_id'=> new \MongoDB\BSON\ObjectId($_id)];
        $query = new \MongoDB\Driver\Query($filter);
        $data = $manager->executeQuery('php.comment', $query);
        foreach($data as $k => $v){
            $arr[$k]=$v;
        }
        return view('',compact('id','arr'));
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
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $bulk = new \MongoDB\Driver\BulkWrite;
        $bulk->insert(['o_id'=>$data['id'],'reply'=>$data['reply'],'username'=>$username]);
        $manager->executeBulkWrite('php.reply', $bulk);
        return $this->success('评论成功','news/index');
    }
    public function viewreply(string $_id){
        $manager = new \MongoDB\Driver\Manager("mongodb://phpuser:phppwd@139.196.225.118:27017/php");
        $id = $_id;
        $filter = ['o_id'=>$_id];
        $query = new \MongoDB\Driver\Query($filter);
        $data = $manager->executeQuery('php.reply', $query);
        $key=0;
        foreach($data as $k =>$v){
            $arr[$k]=$v;
            $key=1;
        }
        $filter = ['_id'=> new \MongoDB\BSON\ObjectId($_id)];
        $query = new \MongoDB\Driver\Query($filter);
        $data = $manager->executeQuery('php.comment', $query);
        $array = [];
        foreach($data as $k){
            $array=$k;
        }
        $username=$array->{'username'};
        $comment=$array->{'body'};
        return view('',compact('id','arr','key','comment','username'));
    }
}
