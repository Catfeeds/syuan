<?php
namespace Common\Model;
use Common\Model\CommonModel;
class ApiModel extends CommonModel{

    protected $tableName = 'app_api';

    protected $types = array(
            1 => '整形(INT)', 
            2 => '字符串(STRING)',
            3 => '浮点型(FLOAT)',
            4 => '布尔型(BOOLEAN)',
            5 => '日期(DATE)',
            6 => '日期时间(DATETIME)',
            7 => '数组(Array)',
            8 => '对象(Object)',
            9 => '枚举类型(ENUM)',
            10 => '常量(CONST)'
        );


    //自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('name', 'require', '请输入接口名称', 1),
		array('version', '1,10000', '请输入正确的数字版本号', 1, 'length'),
		array('path', 'require', '请输入请求路径', 1),
		array('method', array('GET', 'POST', 'PUT', 'DELETE'), '请求方法错误', 1, 'in'),
        array('oauth', array(0, 1), '是否需要登录状态错误', 1, 'in'),
		array('warning', 'require', '请输入接口注意事项', 1),
		array('introduce', 'require', '请输入接口说明', 1),
        array('status', array(0, 1), '发布状态错误', 1, 'in')
	);

    function getTypes() {
        return $this->types;
    }
}
