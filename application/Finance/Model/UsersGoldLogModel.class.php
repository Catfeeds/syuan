<?php
namespace Finance\Model;
use Common\Model\CommonModel;

class UsersGoldLogModel extends CommonModel {
	
	protected $tableName = 'users_gold_log';
	
	/**
	 * 返回财务类型
	 * @return array
	 */
	function getTypes() {
		return array(
				'1' => '奖励',
				'2' => '消耗'
			);
	}


	/**
	 * 返回积分类型
	 * @return array
	 */
	function getCategorys() {
		return array(
			'1' => array('recommend' => '推荐'),
			'2' => array()
		);
	}
}