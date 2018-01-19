<?php
// +---------------------------------------------------------------------
// | BainiuCMS [ WE CAN DO IT MORE SIMPLE ]
// +---------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.bainiu.com All rights reserved.
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Author: lezhizhe_net <lezhizhe_net@163.com>
// +---------------------------------------------------------------------
namespace Common\Behavior;
use Think\Behavior;
use Think\Hook;

// 初始化钩子信息
class InitHookBehavior extends Behavior {

    // 行为扩展的执行入口必须是run
    public function run(&$content){
        if(isset($_GET['g']) && strtolower($_GET['g']) === 'install') {
            return false;
        }
        
        $data = S('hooks');
        if(!$data){
           is_array($plugins = M('Plugins')->where("status=1")->getField("name,hooks"))?null:$plugins = array();
           foreach ($plugins as $plugin => $hooks) {
                if($hooks){
                	$hooks=explode(",", $hooks);
                	foreach ($hooks as $hook){
                		Hook::add($hook,$plugin);
                	}
                }
            }
            S('hooks',Hook::get());
        } else {
           Hook::import($data,false);
        }
        if(defined('MODE_NAME') && MODE_NAME == 'cli' && !defined('__APP__')) {
            $op = sp_get_site_options();
            if($op && isset($op['site_host']) && $op['site_host']) {
                $host = $op['site_host'];
                if(substr($host, -1) == '/') {
                    $host = substr($host, 0, strlen($host) - 1);
                }
                define('__APP__', $host);
            }
        }
    }
}