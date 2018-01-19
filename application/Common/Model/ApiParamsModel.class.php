<?php
namespace Common\Model;
use Common\Model\CommonModel;
class ApiParamsModel extends CommonModel{

    protected $tableName = 'app_api_params';

    //自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
	);

	function delByApiId($api_id) {

	}
}
