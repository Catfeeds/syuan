<?php

//开启调试模式
define("APP_DEBUG", true);

define('MODE_NAME', 'cli');

//网站当前路径
define('SITE_PATH', dirname(__FILE__)."/");

//项目路径，不可更改
define('APP_PATH', SITE_PATH . 'application/');

//项目相对路径，不可更改
define('SPAPP_PATH',   SITE_PATH.'simplewind/');
//
define('SPAPP',   './application/');
//项目资源目录，不可更改
define('SPSTATIC',   SITE_PATH.'statics/');
//定义缓存存放路径
define("RUNTIME_PATH", SITE_PATH . "data/cliruntime/");
//定义程序日志存放目录
define("LOG_DIR", SITE_PATH . "data/logs/cli/");
//版本号
define("CMS_VERSION", '1.0');

define('TIMESTAMP', time());

//载入框架核心文件
include SITE_PATH . 'vendor/autoload.php';
require SPAPP_PATH.'Core/ThinkPHP.php';