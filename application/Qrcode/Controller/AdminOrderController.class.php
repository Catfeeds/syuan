<?php

namespace Qrcode\Controller;
use Common\Controller\AdminbaseController;
class AdminOrderController extends AdminbaseController {
	function _initialize() {
		parent::_initialize();
		$this->status_arr = array(
			'1' => '待审核',
			'2' => '印刷中',
			'3' => '已完成',
			'4' => '已取消'
		);
	}

	public function index() {
		$where  = array('status'=>array('gt',0));
		$status = I('status', 0, 'int');
		$name   = I('name', '', 'trim');
		if($name) {
			$pid = M('Product')->where(array('name'=>array('like','%'.$name.'%')))->getField('pid');
			$where['pid'] = $pid;
		}
		if($status) {
			$where['status'] = $status;
		}
 		$count = M("Order")->where($where)->count();
    	$page  = $this->page($count, 20);
    	$list  = M("Order")->where($where)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
    	$uids = $users = $pids = $pro = array();
		foreach ($list as $key => $value) {
			$uids[$value['uid']] = $value['uid'];
			$pids[$value['pid']] = $value['pid'];
			$list[$key]['createtime'] = date('Y-m-d H:i',$value['createtime']);
			$list[$key]['qrcode'] = M('Order_qrcode')->where(array('orderid'=>$value['id']))->count();
		}
		if($uids) {
			$users = M('Users')->where(array('id' => array('in', $uids)))->getField('id,user_nicename');
		}
		if($pids) {
			$pro = M('Product')->where(array('pid' => array('in', $pids)))->getField('pid,name');
		}
		$this->assign('users', $users);
		$this->assign('pro', $pro);
		$this->assign('list', $list);
		$this->assign('status', $status);
		$this->assign('name', $name);
		$this->assign('page', $page->show('Admin'));
		$this->display();
	}

	public function edit() {
		if(empty($_POST)) {
			$id = I('id', 0, 'int');
			$info = M('Order')->find($id);
			$info['proname']  = M('Product')->where(array('pid'=>$info['pid']))->getField('name');
			$info['username'] = M('Users')->where(array('id'=>$info['uid']))->getField('user_nicename');
			$this->assign('info', $info);
			$this->display();
		} else {
			$num    = I('num', 0, 'int');
			$money  = I('money', 0, 'floatval');
			$status = I('status', 0, 'int');
			$id     = I('id', 0, 'int');
			if($num) {
				$data['num'] = $num;
			}
			if($money) {
				$data['money'] = $money;
			}
			if($status) {
				$data['status'] = $status;
			}
			if($data) {
				$data['id'] = $id;
			} else {
				$this->success('修改成功！',UU('index'));
			}
			if(M('Order')->save($data)!==false) {
				$this->success('修改成功！',UU('index'));
			} else {
				$this->error('修改失败！');
			}
		}
	}

	public function export() {
		$id = I('id', 0, 'int');
		if($id > 0) {
			$order = M('Order')->find($id);
			$min   = M('Order_qrcode')->where(array('orderid'=>$id))->min('listorder');
			$max   = M('Order_qrcode')->where(array('orderid'=>$id))->max('listorder');
			header("Content-type:application/octet-stream"); 
			header("Accept-Ranges:bytes"); 
			header("Content-Disposition: attachment; filename=".$order['batch'].'.txt');
			header("Expires:0"); 
			header("Cache-Control:must-revalidate,post-check=0,pre-check=0 "); 
			header("Pragma:public "); 
			for($i=$min;$i<=$max;$i++) {
				$list = M('Order_qrcode')->where(array('isdel'=>0,'orderid'=>$id,'listorder'=>array('egt',$i)))->order('listorder asc')->limit(100)->select();
				if($list) {
					foreach($list as $k => $v) {
						echo UU('Qrcode/company/index',array('id'=>$v['id'],'code'=>$v['code']),true,true).' '.(empty($v['batch']) ? $order['batch'] : $v['batch']).'-'.$v['block'].'-'.$v['listorder']."\r\n";
						$i = $v['listorder'];
					}
				} else {
					$i = $i+20;
				}
			}
			exit(0);
		}
	}

	public function chart() {
		$starttime = I('starttime');
		$endtime   = I('endtime');
		$status    = I('status', 2, 'int');
		$sm = $em = 0;
		if($starttime) {
			$stime = strtotime($starttime);
			$sm    = date('m', $stime);
		} else {
			$stime = strtotime(date('Y-m').'-01');
			$sm    = date('m', time());
		}
		if($endtime) {
			$etime = strtotime($endtime);
			$em    = date('m', $etime);
		} else {
			$etime = strtotime(date('Y-m-d'));
			$em    = date('m', time());
		}
		$start = $stime;
		$end   = $etime;
		$list  = array();
		while($start != $end) {
			$where = array();
			if($status) {
				$where['status'] = $status;
			}
			if($sm!=$em) {
				//月视图
				$next  = mktime(0,0,0,date('m',$start)+1,1,date('Y',$start))-1;
				$label = date('Y-m', $start);
			} else {
				//日视图
				$next = $start + 86400;
				$label = date('Y-m-d', $start);
			}
			$where['createtime'][] = array('gt', $start);
			$where['createtime'][] = array('lt', $next);
			$c = M('Order')->where($where)->count('id');
			$list[] = array(
				'count' => $c,
				'date'  => $label,
			);
			if($sm!=$em) {
				if(date('m', $next) == date('m', $end)) {
					$start = $end;
				} else {
					$start = $next+1;
				}
			} else {
				$start = $next;
			}
		}
		$this->assign('status', $status);
		$this->assign('list', $list);
		$this->display();
	}

	public function count() {
		$list = M('Order')->field('count(id) as total,uid')->group('uid')->select();
		$uids = $users = array();
		foreach($list as $k => $v) {
			$uids[$v['uid']] = $v['uid'];
			$list[$k]['qrcode'] = M('Order_qrcode')->where(array('uid'=>$v['uid']))->count();
			//总浏览
			$list[$k]['view']   = M('Order_view')->where(array('company'=>$v['uid']))->count();
		}
		$users = M('Users')->where(array('id'=>array('in', $uids)))->getField('id,user_nicename');
		$this->assign('users', $users);
		$this->assign('list', $list);
		$this->display();
	}

	public function count_red() {
		$list = M('Redbag_order')->field('count(`id`) as total,sum(`num`) as num,`uid`,sum(`amount`*`num`) as money')->group('uid')->select();
		$uids = $users = array();
		foreach($list as $k => $v) {
			$uids[$v['uid']] = $v['uid'];
			//总浏览
			$list[$k]['view'] = M('Redbag')->where(array('uid'=>$v['uid'],'winner'=>array('neq','0')))->count();
		}
		$users = M('Users')->where(array('id'=>array('in', $uids)))->getField('id,user_nicename');
		$this->assign('users', $users);
		$this->assign('list', $list);
		$this->display();
	}

	public function count_detail() {
		$uid = I('uid', 0, 'int');
		$starttime = I('starttime');
		$endtime   = I('endtime');
		if($uid > 0) {
			$where['uid'] = $uid;
		}
		$totalsize = M('Order')->where($where)->count();
       	$page  = $this->page($totalsize, 20);
        $list = M('Order')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();
        $pids = $pro = $uids = $com = $subwhere = array();
        if($starttime) {
			$subwhere['createtime'][] = array('gt', $starttime);
		}
		if($endtime) {
			$subwhere['createtime'][] = array('lt', $endtime);
		}
        foreach($list as $k => $v) {
        	$uids[$v['uid']] = $v['uid'];
            $list[$k]['count'] = M('Order_qrcode')->where(array('orderid'=>$v['id']))->count();

            $subQuery = M('order_view')->field('id')->where(array_merge($subwhere,array('orderid'=>$v['id'])))->group('qrcode')->buildSql();
            $times = M('order_view')->table($subQuery.' a')->count();
            if($list[$k]['count']) {
                $list[$k]['percent'] = round($times/$list[$k]['count'], 2);
            } else {
                $list[$k]['percent'] = 0;
            }
            $pids[$v['pid']] = $v['pid'];
        }
        if($uids)
        $com   = M('Users')->where(array('id' => array('in', $uids)))->getField('id,user_nicename');
		$users = M('Users')->where(array('user_type'=>3, 'user_status'=>1))->select();
		$this->assign('users', $users);
		$this->assign('com', $com);
		$this->assign('starttime', $starttime);
		$this->assign('endtime', $endtime);
		$this->assign('uid', $uid);
		$this->assign('list', $list);
		$this->assign('page', $page->show('Admin'));
		$this->display();
	}
}