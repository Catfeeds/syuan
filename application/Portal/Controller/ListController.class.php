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
 * 文章列表
*/
class ListController extends HomebaseController {

	//文章列表页
	public function index() {
		$term=sp_get_term($_GET['id']);
		
		if(empty($term)){
		    header('HTTP/1.1 404 Not Found');
		    header('Status:404 Not Found');
		    if(sp_template_file_exists(MODULE_NAME."/404")){
		        $this->display(":404");
		    }
		    	
		    return ;
		}
		
		$tplname=$term["list_tpl"];
    	$tplname=sp_get_apphome_tpl($tplname, "list");
    	$this->assign($term);
    	$this->assign('cat_id', intval($_GET['id']));
    	$this->display(":$tplname");
	}
	
	public function nav_index(){
		$navcatname="文章分类";
        $term_obj= M("Terms");

        $where=array();
        $where['status'] = array('eq',1);
        $terms=$term_obj->field('term_id,name,parent')->where($where)->order('term_id')->select();
		$datas=$terms;
		$navrule = array(
		    "id"=>'term_id',
            "action" => "Portal/List/index",
            "param" => array(
                "id" => "term_id"
            ),
            "label" => "name",
		    "parentid"=>'parent'
        );
		return sp_get_nav4admin($navcatname,$datas,$navrule);
	}
	
}
