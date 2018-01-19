<?php
namespace Common\Model;
use Common\Model\CommonModel;
class ApiResponseModel extends CommonModel{

    protected $tableName = 'app_api_response';

    //自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
	);

	function delByApiId($api_id) {
		
	}

    function delById($id) {
        $ids = $this->where(array('parentid' => $id))->getField('id,name');
        if($ids) {
            foreach($ids as $pid => $name) {
                $this->delById($pid);
            }
        }
        $this->delete($id);
    }

    function getResponseWidthChild($api_id, $parentid = 0) {
        $list = $this->where(array('api_id' => $api_id, 'parentid' => $parentid))->order('listorder asc')->select();
        if($list) {
            foreach($list as $key => $val) {
                $val['child'] = $this->getResponseWidthChild($api_id, $val['id']);
                $list[$key] = $val;
            }
        }
        return $list;
    }
}
