<?php
namespace Wechat\Model;
use Common\Model\CommonModel;

class WxFansLabelModel extends CommonModel {
	
	protected $tableName = 'wechat_fans_label';
	
	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('original_id', 'require', '请选择当前公共号！', 0),
			array('name', '', '标签名称不能重复！', 1, 'unique', 3)
	);
	
	//自动完成
	protected $_auto = array (
		array('update_at', 'getDateTime', 3, 'callback')
	);
}