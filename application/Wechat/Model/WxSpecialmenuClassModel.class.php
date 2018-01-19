<?php
namespace Wechat\Model;
use Common\Model\CommonModel;

class WxSpecialmenuClassModel extends CommonModel {
	
	protected $tableName = 'wechat_specialmenu_class';

	protected $client_platform_type = array(
		1	=> 	'IOS',
		2	=> 	'Android',
		3	=>	'其他'
	);
	
	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('name', 'require', '规则名称不能为空！', 1),
		array('original_id', 'require', '请选择当前公共号！', 0),
		array('sex', array(0, 1, 2), '性别错误！', 1, 'in'),
	);
	
	/*获取手机操作类型*/
	public function getClientPlatformTypes() {
		return $this->client_platform_type;
	}
}