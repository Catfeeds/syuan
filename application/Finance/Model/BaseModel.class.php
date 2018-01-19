<?php
namespace Finance\Model;

use Common\Model\CommonModel;

class BaseModel extends CommonModel {
	
	/**
	 * 返回财务类型
	 * @return array
	 */
	function getFinanceTypes() {
		return array(
				'1' => '消费',
				'2' => '充值',
				'3' => '提现',
				'4' => '退款'
			);
	}

	/**
	 * 账户金额分转换为元
	 * @param integer $money 用户UID
	 * @return float
	 */
	public static function format_out($money) {
		if($money > 0) {
			return floatval($money / 100);	
		}
		return $money;
	}
}