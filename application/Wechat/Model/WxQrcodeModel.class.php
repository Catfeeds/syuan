<?php
namespace Wechat\Model;
use Common\Model\CommonModel;

class WxQrcodeModel extends CommonModel {
	
	protected $tableName = 'wechat_qrcode';
	
	//二维码类型
	protected $_type = array(1 => '临时二维码', 2 => '永久二维码(数字)', 3 => '永久二维码(字符串)');
	
	//二维码业务类型
	protected $_category = array('recommend' => '推荐');
	
	//自动完成
	protected $_auto = array (
         array('create_at', 'getDateTime', 1, 'callback'),
         array('update_at', 'getDateTime', 3, 'callback'), 
	);
	
	/**
	 * 通过sceneid获取二维码信息
	 * @param string $original_id 对应公众号原始ID
	 * @param string $sceneid
	 * @return array
	 */
	public function getBySceneid($original_id, $sceneid) {
		$result = $this->where(array('original_id' => $original_id, 'sceneid' => $sceneid))->find();
		if($result && $result['data']) {
			$result['data'] = unserialize($result['data']);
		}
		return $result;
	}

	/**
	 * 通过对应功能的获取二维码信息
	 * @param string $original_id 对应公众号原始ID
	 * @param string $category
	 * @param string $openid
	 * @param boolean $forever
	 * @return array
	 */
	public function getByCategoryOpenid($original_id, $category, $openid = '', $forever = false) {
		$where = array('original_id' => $original_id, 'category' => $category);
		if($openid) {
			$where['openid'] = $openid;
		}
		if($forever) {
			$where['type'] = array('GT', 1);
		}
		$result = $this->where($where)->find();
		if($result && $result['data']) {
			$result['data'] = unserialize($result['data']);
		}
		return $result;
	}

	public function getTypes() {
		return $this->_type;
	}
	
	public function getCategorys() {
		return $this->_category;
	}
}
