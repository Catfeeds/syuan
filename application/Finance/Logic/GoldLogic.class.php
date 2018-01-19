<?php

namespace Finance\Logic;

/**
 * 积分/金币处理类
 */
class GoldLogic {
		
	protected $account_model = null;
	protected $gold_model = null;

	function __construct() {
		$this->account_model = D('Finance/UsersAccount');
		$this->gold_model = D('Finance/UsersGoldLog');
	}

	/**
	 * 积分奖励
	 * @param integer $uid 用户UID
	 * @param string $cat 充值方式
	 * @param integer $gold 奖励个数
	 * @param string $remark 奖励备注
	 * @param string $source 来源
	 * @param integer $sourceid 来源ID
	 * @param boolean $transaction 是否方法内使用事务 
	 * @return boolean
	 */
	function charge($uid, $cat, $gold, $remark, $source, $sourceid, $transaction = true) {
		if($transaction) {
			$this->account_model->startTrans();
		}	
		$account = $this->account_model->getByUid($uid, false);
		if($this->account_model->where(array('uid' => $uid))->setInc('gold', $gold) && $this->gold_model->add(array(
					'uid' => $uid,
					'gold' => $gold,
					'type' => 1,
					'category' => $cat,
					'source' => $source,
					'sourceid' => $sourceid,
					'goldbefore' => $account['gold'],
					'goldafter' => $account['gold'] + $gold,
					'remark' => $remark,
					'create_at' => date('Y-m-d H:i:s')
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
	 * 积分消耗
	 * @param integer $uid 用户UID
	 * @param string $cat 消耗方式
	 * @param integer $gold 个数
	 * @param string $remark 备注
	 * @param string $source 来源
	 * @param integer $sourceid 来源ID
	 * @param boolean $transaction 是否方法内使用事务 
	 * @return boolean
	 */
	function consume($uid, $cat, $gold, $remark, $source, $sourceid, $transaction = true) {
		if($transaction) {
			$this->account_model->startTrans();
		}
		$account = $this->account_model->getByUid($uid, false);
		if($account['gold'] >= $gold && $this->account_model->where(array('uid' => $uid, 'gold' => $account['gold']))->setDec('gold', $gold)) {
			if($this->gold_model->add(array(
				'uid' => $uid,
				'gold' => $gold,
				'type' => 2,
				'category' => $cat,
				'source' => $source,
				'sourceid' => $sourceid,
				'goldbefore' => $account['gold'],
				'goldafter' => $account['gold'] - $gold,
				'remark' => $remark,
				'create_at' => date('Y-m-d H:i:s')
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
}