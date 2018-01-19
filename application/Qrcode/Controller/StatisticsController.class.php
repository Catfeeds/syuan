<?php

/**
 * 统计
 */
namespace Qrcode\Controller;
use Common\Controller\MemberbaseController;
class StatisticsController extends MemberbaseController {

	function _initialize(){
		parent::_initialize();
        if($this->user['user_type'] != 3) {
            $this->error('只有企业用户能够使用该功能！');
        }
        $this->status_arr = array(
            '0' => '开始印刷',
            '1' => '待审核',
            '2' => '印刷中',
            '3' => '已完成',
            '4' => '已取消'
        );
	}

	public function index() {
		$type = I('type', 1, 'int');
		if($type == 1) {
			//溯源
			$province = M('Order_view')->field('count(`province`) as total,province')->where(array('company'=>$this->user['id']))->group('province')->select();
			$citys    = M('Order_view')->field('count(`city`) as total,city')->where(array('company'=>$this->user['id']))->group('city')->select(); 
			$this->assign('province', $province);
			$this->assign('city', $citys);
		}
		$this->assign('type', $type);
		$this->display();
	}

	public function scan() {
		$where = array('uid'=>$this->user['id']);
        $cat   = I('category', 0, 'int');
        $name  = I('name', '', 'trim');
        if($cat > 0) {
            $pids = M('Product')->where(array('category'=>$cat,'uid'=>$this->user['id']))->getField('pid');
            if($pids) {
                $condition['cat'] = $cat;
                $where['pid'] = array('in', $pids);
            }
        }
        if($name) {
            $pid = M('Product')->where(array('name'=>array('like', '%'.$name.'%','uid'=>$this->user['id'])))->getField('pid');
            if($pid) {
                $condition['name'] = $name;
                $where['pid'] = array('in', $pid);
            }
        }
        $totalsize = M('Order')->where($where)->count();
        import('Page');
        if($pagesize == 0) {
            $pagesize = 20;
        }
        $PageParam = C("VAR_PAGE");
        $page = new \Page($totalsize,$pagesize);
        $page->setLinkWraper("li");
        $page->__set("PageParam", $PageParam);
        $page->SetPager('default', $pagetpl, array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
        $list = M('Order')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $pids = $pro = array();
        foreach($list as $k => $v) {
            $list[$k]['count'] = M('Order_qrcode')->where(array('orderid'=>$v['id']))->count();
            $subQuery = M('order_view')->field('id')->where(array('orderid'=>$v['id']))->group('qrcode')->buildSql();
            $times = M('order_view')->table($subQuery.' a')->count();
            if($list[$k]['count']) {
                $list[$k]['percent'] = round($times/$list[$k]['count'], 2);
            } else {
                $list[$k]['percent'] = 0;
            }
            $list[$k]['scan'] = M('Order_qrcode')->where(array('orderid'=>$v['id']))->sum('hits');
            $pids[$v['pid']] = $v['pid'];
        }
        if($pids) {
            $pro = M('Product')->where(array('pid'=>array('in',$pids)))->getField('pid,name');
        }
        $category = M('Product_category')->where(array('uid'=>$this->user['id']))->select();
        $this->assign('category', $category);
        $this->assign('pro', $pro);
        $this->assign('list', $list);
        $this->assign('name', $name);
        $this->assign('condition', $condition);
        $this->assign('page', $page->show('default'));
        $this->display();
	}
}