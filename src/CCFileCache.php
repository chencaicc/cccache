<?php  
namespace chencaicc\cccache;

class CCFileCache implements ICache{

    //缓存配置
    private $config=[
        'cache_dir'=>__DIR__.'/runtime',
        'suffix'=>'.php',
        'cache_time'=>3600,
        'md5'=>true,
    ];

    private static $instance=null;

    /*
    获取单例
    */
    public static function getInstance(array $config=[]){

        if(!self::$instance instanceof self){
            self::$instance = new self();
            // 首次实例化的时候要检查下目录权限
            self::$instance->cacheDirCheck();
        }

        foreach($config as $k=>$v){
            self::$instance->config[$k]=$v;
        }

        // 如果修改缓存目录，则需要重新检测目录权限
        if(isset($config['cache_dir']) && self::$instance->config['cache_dir'] != $config['cache_dir']){
            self::$instance->cacheDirCheck();
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
    检测缓存配置是不是正确
    */
    private function cacheDirCheck(){
        if(!is_dir($this->config['cache_dir'])){
            throw new \Exception('缓存目录不存在！');
        }
        if(!is_writable($this->config['cache_dir'])){
            throw new \Exception('缓存目录没有权限！');
        }
    }      

    //设置缓存   
    public function set($key,$val,$life_time=null){
        $key = $this->config['md5'] ? md5($key) : $key;
        $life_time = $life_time ? $life_time : $this->config['cache_time'];

        $file = $this->config['cache_dir'].'/'.$key.$this->config['suffix'];
        $val = serialize($val);

        file_put_contents($file,$val);
        chmod($file,0777);
        touch($file,time()+$life_time);// or $this->error(__line__,"更改文件时间失败"); 
        return true;
    }   

    //得到缓存   
    public function get($key){   
        $this->clearOverdue();   
        if( $this->_isset($key) ){   
            $key_md5 = $this->config['md5'] ? md5($key) : $key;  
            $file = $this->config['cache_dir'].'/'.$key_md5.$this->config['suffix'];   
            $val = file_get_contents($file);   
            return unserialize($val);   
        }   
        return null;   
    }        

    //删除   
    public function delete($key){   
        if( $this->_isset($key) ){   
            $key_md5 = $this->config['md5'] ? md5($key) : $key;  
            $file = $this->config['cache_dir'].'/'.$key_md5.$this->config['suffix'];  
            return @unlink($file);   
        }   
        return false;   
    }   

    //清空所有缓存   
    public function deleteAll(){  
        $files = scandir($this->config['cache_dir']);  
        foreach ($files as $val){   
            @unlink($this->config['cache_dir']."/".$val);   
        }  
    } 

    //判断文件是否有效   
    public function _isset($key){   
        $key = $this->config['md5'] ? md5($key) : $key;   
        $file = $this->config['cache_dir'].'/'.$key.$this->config['suffix'];   
        if( file_exists($file) ){   
            if( @filemtime($file) >= time() ){   
                return true;   
            }else{   
                @unlink($file);   
                return false;   
            }   
        }   
        return false;  
    }

    //清除过期缓存文件   
    public function clearOverdue(){   
        $files = scandir($this->config['cache_dir']);  
        foreach ($files as $val){   
            if (@filemtime($this->config['cache_dir']."/".$val) < time()){   
                @unlink($this->config['cache_dir']."/".$val);   
            }  
        }   
    }



}   

?>  