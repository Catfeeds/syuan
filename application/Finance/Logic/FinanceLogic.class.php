<?php

namespace Finance\Logic;

/**
 * 财务处理类
 */
class FinanceLogic {
		
	protected $account_model = null;
	protected $finance_model = null;

	function __construct() {
		$this->account_model = D('Finance/UsersAccount');
		$this->finance_model = D('Finance/UsersFinance');
	}

	/**
	 * 账号充值
	 * @param integer $uid 用户UID
	 * @param string $cat 充值方式
	 * @param integer $money 充值金额, 单位分
	 * @param string $remark 充值备注
	 * @param integer $orderid 充值ID
	 * @param boolean $transaction 是否方法内使用事务 
	 * @return boolean
	 */
	function charge($uid, $cat, $money, $remark, $orderid, $transaction = true) {
		if($transaction) {
			$this->account_model->startTrans();
		}	
		$account = $this->account_model->getByUid($uid, false);
		if($this->account_model->where(array('uid' => $uid))->setInc('money', $money) && $this->finance_model->add(array(
					'uid' => $uid,
					'money' => $money,
					'type' => 2,
					'category' => $cat,
					'accountbefore' => $account['money'] + $account['frozen_money'],
					'accountafter' => $account['money'] + $account['frozen_money'] + $money,
					'create_at' => date('Y-m-d H:i:s'),
					'remark' => $remark,
					'orderid' => $orderid
				))) {
			if($transaction) {
				$this->account_model->commit();
			}
			return true;
		}
		if($transaction) {
			$this->account_model->rollback();
		}
		return false;
	}

	/**
	 * 账号消费
	 * @param integer $uid 用户UID
	 * @param string $cat 充值方式
	 * @param integer $money 充值金额, 单位分
	 * @param string $remark 充值备注
	 * @param integer $orderid 充值ID
	 * @param boolean $transaction 是否方法内使用事务 
	 * @return boolean
	 */
	function consume($uid, $cat, $money, $remark, $orderid, $transaction = true) {
		if($transaction) {
			$this->account_model->startTrans();
		}
		$account = $this->account_model->getByUid($uid, false);
		if($account['money'] >= $money && $this->account_model->where(array('uid' => $uid, 'money' => $account['money']))->setDec('money', $money)) {
			if($this->finance_model->add(array(
				'uid' => $uid,
				'money' => $money,
				'type' => 1,
				'category' => $cat,
				'accountbefore' => $account['money'] + $account['frozen_money'],
				'accountafter' => $account['money'] + $account['frozen_money'] - $money,
				'create_at' => date('Y-m-d H:i:s'),
				'remark' => $remark,
				'orderid' => $orderid
			))) {
				if($transaction) {
					$this->account_model->commit();
				}
				return true;
			}
		}
		if($transaction) {
			$this->account_model->rollback();
		}
		return false;
	}

	/**
	 * 资产冻结
	 * @param integer $uid 用户UID
	 * @param integer $money 充值金额, 单位分
	 * @param boolean $transaction 是否方法内使用事务 
	 * @return boolean
	 */
	function frozen($uid, $money, $transaction = true) {
		if($transaction) {
			$this->account_model->startTrans();
		}
		$account = $this->account_model->getByUid($uid, false);
		if($account['money'] >= $money && $this->account_model->where(array('uid' => $uid, 'money' => $account['money']))->setInc('frozen_money', $money) && $this->account_model->where(array('uid' => $uid, 'money' => $account['money']))->setDec('money', $money)) {
			if($transaction) {
				$this->account_model->commit();
			}
			return true;
		}
		if($transaction) {
			$this->account_model->rollback();
		}
		return false;
	}

	/**
	 * 资产解除冻结
	 * @param integer $uid 用户UID
	 * @param integer $frozen_money 充值金额, 单位分
	 * @param boolean $transaction 是否方法内使用事务 
	 * @return boolean
	 */
	function unfrozen($uid, $frozen_money, $transaction = true) {
		if($transaction) {
			$this->account_model->startTrans();
		}
		$account = $this->account_model->getByUid($uid, false);
		if($account['frozen_money'] >= $frozen_money && $this->account_model->where(array('uid' => $uid, 'frozen_money' => $account['frozen_money']))->setInc('money', $frozen_money) && $this->account_model->where(array('uid' => $uid, 'frozen_money' => $account['frozen_money']))->setDec('frozen_money', $frozen_money)) {
			if($transaction) {
				$this->account_model->commit();
			}
			return true;
		}
		if($transaction) {
			$this->account_model->rollback();
		}
		return false;
	}

	/**
     * 提现
     * @param integer $uid 用户UID
	 * @param string $bank 体现银行
	 * @param integer $money 金额, 单位分
	 * @param string $remark 备注
	 * @param integer $orderid 体现申请ID
	 * @param boolean $transaction 是否方法内使用事务 
	 * @return boolean
	 */
	function cashout($uid, $bank, $money, $remark, $orderid, $transaction = true) {
		if($transaction) {
			$this->account_model->startTrans();
		}
		$account = $this->account_model->getByUid($uid, false);
		if($account['frozen_money'] >= $money && $this->account_model->where(array('uid' => $uid, 'frozen_money' => $account['frozen_money']))->setDec('frozen_money', $money)) {

			if($this->finance_model->add(array(
				'uid' => $uid,
				'money' => $money,
				'type' => 3,
				'category' => $bank,
				'accountbefore' => $account['money'] + $account['frozen_money'],
				'accountafter' => $account['money'] + $account['frozen_money'] - $money,
				'create_at' => date('Y-m-d H:i:s'),
				'remark' => $remark,
				'orderid' => $orderid
			))) {
				if($transaction) {
					$this->account_model->commit();
				}
				return true;
			}
		}
		if($transaction) {
			$this->account_model->rollback();
		}
		return false;
	}
	
	/**
	 * 退款
	 * @param integer $uid 用户UID
	 * @param string $cat 退款类型
	 * @param integer $money 金额, 单位分
	 * @param string $remark 备注
	 * @param integer $orderid 相关订单ID
	 * @param boolean $transaction 是否方法内使用事务 
	 * @return boolean
	 */
	function refund($uid, $cat, $money, $remark, $orderid, $transaction = true) {
		if($transaction) {
			$this->account_model->startTrans();
		}	
		$account = $this->account_model->getByUid($uid, false);
		if($this->account_model->where(array('uid' => $uid))->setInc('money', $money) && $this->finance_model->add(array(
					'uid' => $uid,
					'money' => $money,
					'type' => 4,
					'category' => $cat,
					'accountbefore' => $account['money'] + $account['frozen_money'],
					'accountafter' => $account['money'] + $account['frozen_money'] + $money,
					'create_at' => date('Y-m-d H:i:s'),
					'remark' => $remark,
					'orderid' => $orderid
				))) {
			if($transaction) {
				$this->account_model->commit();
			}
			return true;
		}
		if($transaction) {
			$this->account_model->rollback();
		}
		return false;
	}
}
