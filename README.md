# cccache #

测试用例：使用文件或者redis对数据进行缓存


## 如何安装 ##

composer require "chencaicc/cccache:1.0.0"


## 必须使用composer加载 ##

```php
<?php
require "vendor/autoload.php";
use chencaicc\cccache\CCFileCache;
use chencaicc\cccache\CCRedis;

```

### 一般使用 ###

```php

//文件缓存方式
$cache_obj=\chencaicc\cccache\CCFileCache::getInstance();

$ok = $cache_obj->set('username','小新',10*60);
$name = $cache_obj->get('username');

var_dump($ok);
echo '<br>';
var_dump($name);

echo '<hr>';


//redis缓存
$cache_obj=\chencaicc\cccache\CCRedis::getInstance();

$ok = $cache_obj->set('username','小新',10*60);
$name = $cache_obj->get('username');

var_dump($ok);
echo '<br>';
var_dump($name);




//删除一个键
$cache_obj->delete('username');

// 删除所有键
$cache_obj->deleteAll();


```


### php反射方式使用 ###



```php
//参数配置
$system_config=[

  'cache'=>'file',
    // 'cache_dir'=>__DIR__.'/runtime',
    'suffix'=>'.php',
    'cache_time'=>3600,
    'md5'=>true,



  // 'cache'=>'redis',
    'cache_time'=>3*60,//单位秒
    'ip'=>'127.0.0.1',
    'port'=>6379,
    'password'=>null,
    'db'=>0,
];


if($system_config['cache'] == 'file'){
  $cache_class  = new \ReflectionClass('chencaicc\cccache\CCFileCache');
}elseif($system_config['cache'] == 'redis'){
  $cache_class  = new \ReflectionClass('chencaicc\cccache\CCRedis');
}else{
  exit('您配置的缓存方式不支持！');
}

$get_instance_method = $cache_class->getMethod('getInstance');


// 获取cache对象
$cache_obj = $get_instance_method->invoke($cache_class,$system_config);


//添加缓存和获取缓存
$key = 'username';
for($i=0;$i<4;$i++){
  $set = $cache_obj->set($key.$i,'小新',10*60);
  $get = $cache_obj->get($key.$i);
  var_dump($set);
  var_dump($get);
}

//删除一个键或者删除所有缓存
$cache_obj->delete('username0');
$cache_obj->deleteAll($key);

echo '<br>';

$username0 = $cache_obj->get('username0');
var_dump($username0);
$username2 = $cache_obj->get('username2');
var_dump($username2);

```



### 写在最后 ###

该库使用了单例模式以及适配器模式简单完成了数据缓存的功能。
