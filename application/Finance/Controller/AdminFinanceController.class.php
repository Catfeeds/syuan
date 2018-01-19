<?php

/**
 * 财务中心
 */
namespace Finance\Controller;
use Common\Controller\AdminbaseController;
class AdminFinanceController extends AdminbaseController {

	function _initialize() {
		parent::_initialize();
	}
	
    /*用户财务*/
	public function users_index() {
		
		$where = array();
		$uid = I('uid', 0, 'intval');
		$mobile = I('mobile', '', 'trim');
		$typevalue = I('typevalue', 0, 'intval');
		$starttime = I('starttime');
		$endtime = I('endtime');
		if($uid > 0) {
			$where['uid'] = $uid;
		} else {
			$uid = '';
		}
		if($mobile) {
			$where['mobile'] = $mobile;
		}
		if($typevalue > 0) {
			$where['type'] = $typevalue;
		}
		if($starttime && $endtime && ($endtime > $starttime)) {
			$where['create_at'] = array('BETWEEN', array($starttime, date('Y-m-d H:i:s', strtotime($endtime) + 86400)));
		}
		if($starttime && !$endtime) {
			$where['create_at'] = array('EGT', $starttime);
		}
		if(!$starttime && $endtime) {
			$where['create_at'] = array('ELT', $endtime);
		}
		$count = D('UsersFinanceView')->field('id')->where($where)->count();
		$page = $this->page($count, 20);
		$list = D('UsersFinanceView')->where($where)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		if($list) {
			$category = array(2 => array(), 3 => array());
			$category[2] = M("PaymentConfig")->where()->getField('type,name', true);
			$category[3] = C('Finance.CASHOUT_BANK');
			foreach($list as $key => $val) {
				if(isset($category[$val['type']][$val['category']])) {
					$val['category'] = $category[$val['type']][$val['category']];
				}
				$val['money'] = D('Finance/UsersFinance')->format_out($val['money']);
				$val['accountbefore'] = D('Finance/UsersFinance')->format_out($val['accountbefore']);
				$val['accountafter'] = D('Finance/UsersFinance')->format_out($val['accountafter']);
				$list[$key] = $val;
			}
		}		
	
		$this->assign('uid', $uid);
		$this->assign('mobile', $mobile);
		$this->assign('starttime', $starttime);
		$this->assign('endtime', $endtime);
		$this->assign('typevalue', $typevalue);
		$this->assign('type', D('Finance/UsersFinance')->getFinanceTypes());
		$this->assign("page", $page->show('Admin'));
		$this->assign("list",$list);
    	$this->display();
    }

    /**
     * 充值列表
     */
    function charge() {
    	$where = array();
    	$uid = I('uid', 0, 'intval');
		$type = I('type', '', 'trim');
		$starttime = I('starttime');
		$endtime = I('endtime');
		if($uid > 0) {
			$where['uid'] = $uid;
		} else {
			$uid = '';
		}
		if($type > 0) {
			$where['type'] = $type;
		}
		if($starttime && $endtime && ($endtime > $starttime)) {
			$where['paid_at'] = array('BETWEEN', array($starttime, date('Y-m-d H:i:s', strtotime($endtime) + 86400)));
		}
		if($starttime && !$endtime) {
			$where['paid_at'] = array('EGT', $starttime);
		}
		if(!$starttime && $endtime) {
			$where['paid_at'] = array('ELT', $endtime);
		}

    	$count = D('PaymentCharge')->field('id')->where($where)->count();
    	$page = $this->page($count, 20);
    	$list = D('PaymentCharge')->where($where)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		$users = $uids = array();
		if($list) {
			foreach($list as $key => $val) {
				if($val['uid'] > 0) {
					$uids[$val['uid']] = $val['uid'];
				}
				$val['totaldiff'] = $val['status'] == 1 ? $val['total'] != $val['real_total'] : false;
				$val['total'] = D('Finance/UsersFinance')->format_out($val['total']);
				$val['real_total'] = D('Finance/UsersFinance')->format_out($val['real_total']);
				$list[$key] = $val;
			}
			if($uids) {
				$users = M('Users')->where(array('id' => array('in', $uids)))->getField('id,user_login,mobile');
			}
		}
		$payment = M('PaymentConfig')->where(array('status'=>1))->getField('type,name');

		$this->assign('uid', $uid);
		$this->assign('starttime', $starttime);
		$this->assign('endtime', $endtime);
		$this->assign('type', $type);
    	$this->assign("page", $page->show('Admin'));
		$this->assign("list", $list);
		$this->assign('payment', $payment);
		$this->assign('users', $users);
    	$this->display();
    }

    /**
     * 后台充值
     */
    function users_charge() {
    	$this->display();
    }

    /**
     *后台充值确认
    */
    function users_charge_confirm() {
    	if(IS_POST) {
    		$type = I('type', 'uid', 'trim');
    		$value = I('content', '', 'trim');
    		if(!$value) {
    			$this->error('请输入要搜索的值!');
    		}
    		if(!in_array($type, array('uid', 'mobile'))) {
    			$type = 'uid';
    		}    		
    		if($type == 'uid') {
    			$uid = intval($value);
    			$where = array('id' => $uid);
    		} else {
    			$mobile = $value;
    			$where = array('mobile' => $mobile);
    		}
    		$user = D('Users')->where($where)->find();
    		if($user['id'] > 0) {
    			$url = UU('Finance/AdminFinance/users_charge_confirm', array('uid' => $user['id']));
    			$this->success($url);
    		} else {
    			$this->error('用户不存在!');
    		}
    		return false;
    	}
    	$uid = I('uid', 0, 'intval');
    	$user = D('Users')->find($uid);
    	if(!$user) {
    		$this->error('用户不存在!');
    	}
    	$clist = M('PaymentConfig')->where(array('status' => 1, 'enable' => 1))->getField('type,name');
    	$chargelist = array();
    	if($clist) {
    		foreach($clist as $type => $name) {
    			if($type == 'gift' || strpos($type, 'offline') !== false) {
    				$chargelist[$type] = $name;
    			}
    		}
    	}
    	if(!$chargelist) {
    		$this->error('没有可用充值方式, 请在财务->在线支付 里配置!');
    	}
    	$user['account'] = D('Finance/UsersAccount')->getByUid($uid);
    	$this->assign('user', $user);
    	$this->assign('chargelist', $chargelist);
    	$this->display();
    }

    /**
     * 后台充值提交
     */
    function users_charge_post() {
    	if(IS_POST) {
    		$uid = I('uid', 0, 'intval');
    		$money = intval(I('money', 0, 'floatval') * 100);
    		$type = I('charge', '', 'trim');
    		if($money < 0) {
    			$this->error('充值金额有误!');
    		}
    		$payment = M('PaymentConfig')->where(array('type' => $type, 'status' => 1, 'enable' => 1))->find();
    		if(!$payment) {
    			$this->error('充值方式不存在!');
    		}
    		if($type != 'gift' && strpos($type, 'offline') === false) {
    			$this->error('后台不支持该充值方式!');
    		}
	    	$user = D('Users')->find($uid);
	    	if(!$user) {
	    		$this->error('用户不存在!');
	    	}
	    	$remark = '后台充值:'.$payment['name'];
	    	if(D('Finance/Finance', 'Logic')->charge($uid, $type, $money, $remark, $this->admin['id'])) {
	    		$this->success('充值成功');
	    	} else {
	    		$this->error('充值失败!');
	    	}	
    	}
    	return false;
    }

    /**
     *用户财务
   	 */
    function users_account() {
    	$where = array();
    	$uid = I('uid', 0, 'intval');
		$mobile = I('mobile', '', 'trim');
		if($uid > 0) {
			$where['uid'] = $uid;
		} else {
			$uid = '';
		}
		if($mobile) {
			$result_uid = intval(D('Users')->where(array('mobile' => $mobile))->getField('id'));
			$where['uid'] = $result_uid;
			$this->assign('mobile', $mobile);
		}

		$count = D('Finance/UsersAccount')->field('id')->where($where)->count();
    	$page = $this->page($count, 20);
		$list = D('Finance/UsersAccount')->where($where)->limit($page->firstRow . ',' . $page->listRows)->select();
		if($list) {
			$users = $uids = array();
			foreach($list as $key => $value) {
				$uids[] = $value['uid'];
				$value['frozen_money'] = D('Finance/UsersAccount')->format_out($value['frozen_money']);
				$value['money'] = D('Finance/UsersAccount')->format_out($value['money']);
				$list[$key] = $value;
			}
			if($uids) {
				$users = D('Users')->where(array('id' => array('IN', $uids)))->getField('id,user_login,mobile,user_nicename');
			}
		}
		$total_money = D('Finance/UsersAccount')->format_out(intval(D('Finance/UsersAccount')->sum('money')));
		$total_frozen_money = D('Finance/UsersAccount')->format_out(intval(D('Finance/UsersAccount')->sum('frozen_money')));
		$total_gold = intval(D('Finance/UsersAccount')->sum('gold'));

		$this->assign('total_money', $total_money);
		$this->assign('total_frozen_money', $total_frozen_money);
		$this->assign('total_gold', $total_gold);

		$this->assign('uid', $uid);
		$this->assign('users', $users);
		$this->assign('list', $list);
		$this->assign("page", $page->show('Admin'));
    	$this->display();
    }

    /**
     * 提现列表
     */
    function cashout() {
    	$where = array();
    	$uid = I('uid', 0, 'intval');
		$mod_status = I('status', -1, 'intval');
		$pay_status = I('paystatus', -1, 'intval');
		$starttime = I('starttime');
		$endtime = I('endtime');
		if($uid > 0) {
			$where['uid'] = $uid;
		} else {
			$uid = '';
		}
		if($mod_status >= 0) {
			$where['mod_status'] = $mod_status;
		}
		if($pay_status >= 0) {
			$where['pay_status'] = $pay_status;
		}
		if($starttime && $endtime && ($endtime > $starttime)) {
			$where['create_at'] = array('BETWEEN', array($starttime, date('Y-m-d H:i:s', strtotime($endtime) + 86400)));
		}
		if($starttime && !$endtime) {
			$where['create_at'] = array('EGT', $starttime);
		}
		if(!$starttime && $endtime) {
			$where['create_at'] = array('ELT', $endtime);
		}

    	$count = M('UsersCashout')->field('id')->where($where)->count();
    	$page = $this->page($count, 20);
    	$list = M('UsersCashout')->where($where)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		$users = $uids = array();
		if($list) {
			foreach($list as $key => $val) {
				if($val['uid'] > 0) {
					$uids[$val['uid']] = $val['uid'];
				}
				if($val['mod_uid'] > 0) {
					$uids[$val['mod_uid']] = $val['mod_uid'];
				}
				if($val['pay_uid'] > 0) {
					$uids[$val['pay_uid']] = $val['pay_uid'];
				}
				$val['amount'] = D('Finance/UsersFinance')->format_out($val['amount']);
				$val['fee'] = D('Finance/UsersFinance')->format_out($val['fee']);
				$list[$key] = $val;
			}
			if($uids) {
				$users = M('Users')->where(array('id' => array('in', $uids)))->getField('id,user_login,mobile');
				$amountlist = D('Finance/UsersAccount')->where(array('uid' => array('in', $uids)))->getField('uid,money');
				if($amountlist) {
					foreach($amountlist as $key => $money) {
						$users[$key]['money'] = D('Finance/UsersAccount')->format_out($money);
					}
				}
			}
		}
		
		$this->assign('uid', $uid);
		$this->assign('starttime', $starttime);
		$this->assign('endtime', $endtime);
		$this->assign('mod_status', $mod_status);
		$this->assign('pay_status', $pay_status);
    	$this->assign("page", $page->show('Admin'));
		$this->assign("list", $list);
		$this->assign('financeconfig', C('FINANCE'));
		$this->assign('users', $users);
    	$this->display();
    }

    /*提现审核*/
    function cashout_mod() {
    	$id = I('id', 0, 'intval');
    	$cashout = M('UsersCashout')->find($id);
    	if(!$cashout) {
    		$this->error('数据不存在');
    	}
    	$account = D('Finance/UsersAccount')->getByUid($cashout['uid'], false);
    	if(IS_POST) {
    		if($cashout['mod_status'] > 1) {
    			$this->error('该条记录已经审核过了!');
    		}
    		$mod_status = I('status', 0, 'intval');
    		$mod_msg = I('remark', '', 'trim');
    		if(!in_array($mod_status, array(2, 4))) {
    			$this->error('审核状态有误!');
    		} else if(empty($mod_msg)) {
    			$this->error('请详细填写审核备注');
    		} else if($mod_status == 2 && $cashout['amount'] > $account['money']) {
    			$this->error('账户余额不足, 不能通过审核!');
    		} else {
    			$data = array('mod_status' => $mod_status, 'mod_msg' => $mod_msg, 'mod_uid' => $this->admin['id'], 'mod_at' => date('Y-m-d H:i:s', TIMESTAMP));
    			if($mod_status == 2) {
 					$data['pay_status'] = 1;
 					M('UsersCashout')->startTrans();
    				if(D('Finance/Finance', 'Logic')->frozen($cashout['uid'], $cashout['amount'], false) && M('UsersCashout')->where(array('id' => $id, 'mod_status' => 1))->save($data)) {
    					M('UsersCashout')->commit();
    					$this->success('保存成功, 用户提现资产已冻结!');
    				} else {
    					M('UsersCashout')->rollback();
		    			$this->error('保存失败, 请稍后重试!');
	    			}   			
    			} else {
    				if(M('UsersCashout')->where(array('id' => $id, 'mod_status' => 1))->save($data)) {
	    				$this->success('保存成功!');
	    			} else {	    				
	    				$this->error('保存失败, 请稍后重试!');
	    			}
    			}
    		}
    	}
    	if($cashout['mod_status'] == 0) {
    		M('UsersCashout')->where(array('id' => $id, 'mod_status' => 0))->save(array('mod_status' => 1));
    	}

    	$user = D('Common/Users')->find($cashout['uid']);
    	$account['money'] = D('Finance/UsersAccount')->format_out($account['money']);
    	$account['frozen_money'] = D('Finance/UsersAccount')->format_out($account['frozen_money']);
    	$user['account'] = $account;
    	$cashout['amount'] = D('Finance/UsersAccount')->format_out($cashout['amount']);
    	$cashout['fee'] = D('Finance/UsersAccount')->format_out($cashout['fee']);
    	$this->assign('user', $user);
    	$this->assign('cashout', $cashout);
    	$this->assign('financeconfig', C('FINANCE'));
    	$this->display();
    }

    /*提现转账*/
    function cashout_pay() {
    	$id = I('id', 0, 'intval');
    	$cashout = M('UsersCashout')->find($id);
    	if(!$cashout) {
    		$this->error('数据不存在');
    	}
    	$account = D('Finance/UsersAccount')->getByUid($cashout['uid'], false);
    	if(IS_POST) {
    		if($cashout['mod_status'] != 2) {
    			$this->error('该条记录未审核通过!');
    		}
    		$pay_status = I('status', 0, 'intval');
    		$pay_msg = I('remark', '', 'trim');
    		$pay_at = I('pay_at', '', 'trim');
    		if(!in_array($pay_status, array(2, 3))) {
    			$this->error('转账状态有误!');
    		} else if(empty($pay_msg)) {
    			$this->error('请详细填写转账备注');
    		} else if(empty($pay_at)) {
    			$this->error('请填写转账时间');
    		} else if($pay_status == 2 && $cashout['amount'] > $account['frozen_money']) {
    			$this->error('冻结金额不足, 确认转账失败!');
    		} else {
    			if($pay_status == 2) {
    				M('UsersCashout')->startTrans();
    				$remark = '提现到支付账号:'.$cashout['account'].'('.$cashout['realname'].')';    				
    				$data = array('pay_status' => $pay_status, 'pay_msg' => $pay_msg, 'pay_uid' => $this->admin['id'], 'pay_at' => $pay_at);
    				if(D('Finance/Finance', 'Logic')->cashout($cashout['uid'], $cashout['bank'], $cashout['amount'], $remark, $cashout['id'], false) && M('UsersCashout')->where(array('id' => $id, 'pay_status' => 1))->save($data)) {
    					M('UsersCashout')->commit();
		    			$this->success('保存成功, 用户冻结资产已扣除!');
	    			} else {
	    				M('UsersCashout')->rollback();
	    				$this->error('保存失败: 冻结金额扣除失败, 请稍后重试!');
	    			}
    			} else {
    				M('UsersCashout')->startTrans();
    				$data = array('pay_status' => $pay_status, 'pay_msg' => $pay_msg, 'pay_uid' => $this->admin['id'], 'pay_at' => $pay_at);
    				if(M('UsersCashout')->where(array('id' => $id, 'pay_status' => 1))->save($data) && D('Finance/Finance', 'Logic')->unfrozen($cashout['uid'], $cashout['amount'], false)) {
    					M('UsersCashout')->commit();
    					$this->success('保存成功, 用户冻结资产已恢复!');
	    			} else {
	    				M('UsersCashout')->rollback();
	    				$this->error('保存失败, 请稍后重试!');
	    			}
    			}
    		}
    	}

    	$user = D('Common/Users')->find($cashout['uid']);
		$account['money'] = D('Finance/UsersAccount')->format_out($account['money']);
    	$account['frozen_money'] = D('Finance/UsersAccount')->format_out($account['frozen_money']);
    	$user['account'] = $account;
    	$cashout['amount'] = D('Finance/UsersAccount')->format_out($cashout['amount']);
    	$cashout['fee'] = D('Finance/UsersAccount')->format_out($cashout['fee']);
    	$this->assign('user', $user);
    	$this->assign('cashout', $cashout);
    	$this->assign('financeconfig', C('FINANCE'));
    	$this->display();
    }
}
