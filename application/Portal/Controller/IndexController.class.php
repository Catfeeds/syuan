<?php
// +----------------------------------------------------------------------
// | BainiuCMS [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2014 http://www.bainiu.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: lezhizhe_net <lezhizhe_net@163.com>
// +----------------------------------------------------------------------
namespace Portal\Controller;
use Common\Controller\HomebaseController; 
/**
 * 首页
 */
class IndexController extends HomebaseController {
	
    //首页
	public function index() {
		$this->redirect('qrcode/profile/index');
    	$this->display(":index");
    }

}


