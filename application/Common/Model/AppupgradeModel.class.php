<?php
namespace Common\Model;
use Common\Model\CommonModel;
class AppupgradeModel extends CommonModel{

    protected $tableName = 'app_version_upgrade';
    protected $statusMap = array(0 => '待发布', 1 => '已发布', -1 => '已下线');

    //自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('version_code', 'require', '请输入正确的版本号', 1),
			array('apk_url', 'require', '安装地址不能为空', 1),
			array('upgrade_point', 'require', '升级提示不能为空', 1),
			array('confirm_title', 'require', '确认升级提示文字不能为空', 1),
			array('cancel_title', 'require', '取消按钮提示文字不能为空', 1),
			array('mark', 'require', '请填写版本升级内容', 1),
			array('type', array(0, 1, 2), '升级类型错误', 1, 'in'),
			array('status', array(-1, 0, 1), '发布状态错误', 1, 'in')
	);
	
	//自动完成
	protected $_auto = array (
         array('create_at', 'getDateTime', 1, 'callback'),
         array('update_at', 'getDateTime', 3, 'callback'), 
	);

    function getStatusMap() {
        return $this->statusMap;
    }
}
