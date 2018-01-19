<?php
namespace Finance\Model;

use Finance\Model\BaseModel;

class UsersAccountModel extends BaseModel {
	
	protected $tableName = 'users_account';
	
	/**
	 * 根据UID返回信息
	 * @param integer $uid 用户UID
	 * @param boolean $format 是否格式化money字段[转换为元]
	 * @return array
	 */
	function getByUid($uid, $format = true) {
		$result = $this->where(array('uid' => $uid))->find();
		if(!$result) {
			$result = array('uid' => $uid, 'money' => 0, 'frozen_money' => 0, 'gold' => 0);
			$this->add($result);
		}
		if($format) {
			$result['money'] = $this->format_out($result['money']);
			$result['frozen_money'] = $this->format_out($result['frozen_money']);
		}
		return $result;
	}
}