<?php
namespace chencaicc\cccache;

class CCRedis implements ICache{

    //缓存配置
    private $config=[
        'cache_time'=>86400,//1天
        // 'cache_time'=>3*60,//1天
        'ip'=>'127.0.0.1',
        'port'=>6379,
        'password'=>null,
        'db'=>0,
    ];

    private static $instance=null;
    private static $redis=null;

    /*
    获取单例以及redis连接对象
    */
    public static function getInstance(array $config=[]){

        if(!self::$instance instanceof self){
            self::$instance = new self();
            // 首次实例化的时候要检查下系统是否安装redis扩展
            self::$instance->checkEnvironment();
        }

        foreach($config as $k=>$v){
            self::$instance->config[$k]=$v;
        }

        if(is_null(self::$redis)){
            self::$redis=new \redis();
            self::$redis->connect(self::$instance->config['ip'],self::$instance->config['port']);
            self::$redis->select(self::$instance->config['db']);
            self::$redis->auth(self::$instance->config['password']);
        }
        return self::$instance;
    }

    // 禁止实例化
    private function __construct(){ 
    }
    
    // 禁止克隆
    private function __clone(){
    }




    /*
        环境检测
    */
    private function checkEnvironment(){
        if(!extension_loaded('redis')){
            throw new \Exception('系统未安装redis扩展！');
        }
    }

    //设置缓存   
    public function set($key,$val,$life_time=null){

        self::$redis->set($key,serialize($val));
        self::$redis->expire($key,$life_time ? $life_time : self::$config['cache_time']);

        return true;
    }   

    //得到缓存   
    public function get($key){
        if($this->keyExists($key)){
            $val=self::$redis->get($key);
            return unserialize($val);   
        }else{
            return null;
        }
    }        


    //删除   
    public function delete($key){
        if(!$this->keyExists($key)){
            return true;
        }
        return self::$redis->del($key);   
    }   

    //清空所有缓存   
    public function deleteAll(){ 
        return self::$redis->flushall(); 
    } 


    public function keyExists($key){
        if(self::$redis->exists($key)){
            return true;
        }else{
            return false;
        }
    }


    public function __destruct(){
        self::$redis->close();
    }

}