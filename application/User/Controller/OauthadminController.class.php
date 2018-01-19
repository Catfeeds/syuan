<?php
/**
 * 参    数：
 * 作    者：lht
 * 功    能：OAth2.0协议下第三方登录数据报表
 * 修改日期：2013-12-13
 */
namespace User\Controller;
use Common\Controller\AdminbaseController;
class OauthadminController extends AdminbaseController {
	
	//用户列表
	function index(){

		$fromlist = array('wechat' => '微信', 'sina' => '新浪微博', 'qq' => 'QQ');
		$where = array();
        
        $uid = I('uid', -1, 'intval');
        $name = I('name', '', 'trim');
 		$from = I('from', '', 'trim');
 		$status = I('status', -1, 'intval');

        if($uid > 0) {
            $where['id'] = $uid;
        } else {
            $uid = '';
        }
        if($from) {
            $where['from'] = $from;
        }
        if($name) {
            $where['name'] = array('like', '%'.$name.'%');
        }
        if($status > -1) {
        	$where['status'] = $status;
        }
		
		$count = M('OauthUser')->where($where)->count();
		$page = $this->page($count, 20);
		$lists = M('OauthUser')->where($where)->order("create_time DESC")->limit($page->firstRow . ',' . $page->listRows)->select();

		$this->assign('uid', $uid);
        $this->assign('name', $name);
        $this->assign('from', $from);
        $this->assign('status', $status);
        $this->assign('fromlist', $fromlist);

		$this->assign("page", $page->show('Admin'));
		$this->assign('lists', $lists);
		$this->display();
	}
	
	//删除用户
	function delete(){
		$id=intval($_GET['id']);
		if(empty($id)){
			$this->error('非法数据！');
		}
		$rst = M("OauthUser")->where(array("id"=>$id))->delete();
		if($rst!==false) {
			$this->success("删除成功！", U("oauthadmin/index"));
		} else {
			$this->error('删除失败！');
		}
	}
}