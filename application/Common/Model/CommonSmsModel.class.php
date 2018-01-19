<?php
namespace Common\Model;
use Common\Model\CommonModel;

/*短信基类*/
class CommonSmsModel extends CommonModel {
	
	protected $types = array(
		'1' => '注册',
		'2' => '找回密码',
		'3' => '手机号验证'
	);

	/*获取类型*/
	public function getTypes() {
		return $this->types;
	}
}