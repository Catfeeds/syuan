<?php

/**
 * 会员财务中心
 */
namespace Finance\Controller;
use Common\Controller\MemberbaseController;

class UserController extends MemberbaseController {

	function _initialize(){
		parent::_initialize();
	}
	
    /*用户财务*/
	public function index() {
		$do = I('do', 'account', 'trim');
		if(!in_array($do, array('account', 'list', 'cashout', 'cashoutlist'))) {
			$do = 'account';
		}
		$title = '我的账户';
		$account = D('Finance/UsersAccount')->getByUid($this->user['id']);
		if($do == 'list') {
			$count = D('Finance/UsersFinance')->where(array("uid" => $this->user['id']))->count();
			$page = $this->page($count, 20);
			$list = D('Finance/UsersFinance')->where(array("uid" => $this->user['id']))->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
			if($list) {
				foreach($list as $key=>$val) {
					$val['money'] = D('Finance/UsersFinance')->format_out($val['money']);
					$list[$key] = $val;
				}
			}
			$this->assign('type', D('Finance/UsersFinance')->getFinanceTypes());
			$this->assign("page", $page->show('Home'));
			$this->assign("list",$list);
			$title = '财务记录';
		} else if($do == 'cashout') {
			$financeconfig = C('FINANCE');
			$cashlog = M('UsersCashout')->where(array('uid' => $this->user['id']))->order('id desc')->find();
			if($cashlog) {
				$cashlog['amount'] = D('Finance/UsersAccount')->format_out($cashlog['amount']);
				$cashlog['fee'] = D('Finance/UsersAccount')->format_out($cashlog['fee']);
			}
			$title = '提现';
			$this->assign('financeconfig', $financeconfig);
			$this->assign('cashlog', $cashlog);
		} else if($do == 'cashoutlist') {
			$count = M('UsersCashout')->where(array("uid" => $this->user['id']))->count();
			$page = $this->page($count, 20);
			$list = M('UsersCashout')->where(array("uid" => $this->user['id']))->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
			if($list) {
				foreach($list as $key=>$val) {
					$val['create_at'] = substr($val['create_at'], 0, 10);
					$val['amount'] = D('Finance/UsersAccount')->format_out($val['amount']);
					$val['fee'] = D('Finance/UsersAccount')->format_out($val['fee']);
					$list[$key] = $val;
				}
			}
			$title = '提现记录';
			$this->assign('financeconfig', C('FINANCE'));
			$this->assign("page", $page->show('Home'));
			$this->assign("cashlist",$list);
		}
		$this->assign('title', $title);
		$this->assign('do', $do);
		$this->assign('account', $account);
    	$this->display(':finance');
    }

    public function cashout_post() {
    	if(IS_POST) {
    		$password = I('password', '', 'trim');
    		if(empty($password)) {
    			$this->error('请填写原密码!');
    		}
    		if(md5($password.$this->user['pass_salt']) != $this->user['user_pass']) {
				$this->error('原密码错误!');
    		}
    		$cashlog = M('UsersCashout')->where(array('uid' => $this->user['id']))->order('id desc')->find();
    		if($cashlog && $cashlog['mod_status'] < 2) {
    			$this->error('您有尚未完成的提现申请，不能再次申请!');
    		}
    		$useraccount = D('Finance/UsersAccount')->getByUid($this->user['id']);
    		$amount = I('amount', 0, 'intval');
    		$bank = I('bank', '', 'trim');
    		$account = I('account', '', 'trim');
    		$name = I('name', '', 'trim');
    		if($amount < 1) {
    			$this->error('提现金额不能低于1元');
    		} else if(!preg_match('/^[1-9]\d*$/', $amount)) {
    			$this->error('提现金额必须为整数');
    		} else if($amount > $useraccount['money']) {
    			$this->error('体现金额不能大于账户可用余额');
    		} else if(empty($account)) {
    			$this->error('请填写开户账号');
    		} else if(empty($name)) {
    			$this->error('请填写开户名');
    		} else {
    			$fee = ceil($amount * C('Finance.CASHOUT_FEE'));
    			$amount *= 100;
    			if(M('UsersCashout')->add(array('uid' => $this->user['id'],'amount' => $amount,'fee' => $fee,'bank' => $bank,'account' => $account,'realname' => $name,'create_at' => date('Y-m-d H:i:s', TIMESTAMP)))) {
    				$this->success('您的提现申请已经提交, 请耐心等待审核!');
    			} else {
    				$this->error('保存失败, 请稍后重试!');
    			}
    		}
    	}
    }
}
