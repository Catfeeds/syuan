<?php
namespace Wechat\Model;
use Common\Model\CommonModel;

class WxMenuModel extends CommonModel {
	
	protected $tableName = 'wechat_menues';
	protected $host = '';
	
	//自动回复消息类型
	protected $_types = array(
		'click' => '点击事件',
		'view' => 'URL跳转'
	);
	
	//自动验证
	protected $_validate = array(
		//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
		array('original_id', 'require', '请选择当前公共号！', 0),
		array('type', array('click', 'view'), '菜单类型错误！', 0, 'in'),
		array('name', 'require', '菜单名称不能为空！', 1)
	);

	/**
	 * 获取微信菜单, 可直接调用封装的API接口
	 * @return array | $button = array(
				array('name' => '便民查询',
						'sub_button' => array(
								array('type' => 'click', 'name' => '公交查询', 'key' => 'bus'),
								array('type' => 'view', 'name' => '天气查询', 'url' => 'http://www.baidu.com'),
						)),
				array('type' => 'click',
						'name'	=> '使用帮助',
						'key'	=>	'help'
				)
		);
	 */
	public function getWxMenu($original_id, $catid) {
		$menu = array();
		$parents = $this->getByParentId($original_id, $catid, 0, true);
		foreach($parents as $val) {
			$children = $this->getByParentId($original_id, $catid, $val['id'], true);
			if($children) {
				$item = array('name' => $val['name'], 'sub_button' => array());
				foreach($children as $child) {
					$item['sub_button'][] = $this->_dealMenuChildItem($child);
				}
			} else {
				$item = $this->_dealMenuChildItem($val);
			}
			$menu[] = $item;
		}
		return $menu;
	}
	
	/**
	 * 根据父类ID获取菜单, 如果$display
	 * @param integer $parentid 父类ID
	 * @param boolean $display 是否只取显示的菜单
	 * @return array
	 */
	function getByParentId($original_id, $catid, $parentid, $display = false) {
		$result = array();
		$where = array(
			'original_id' => $original_id,
			'catid' => $catid,
			'parentid' => $parentid
		);
		if($display) {
			$where['status'] = 1;
		}
		return $this->where($where)->order(array("listorder"=>"asc"))->select();
	}
	
	/**
	 * 处理子菜单情况
	 * @param array $item 菜单的数据表存储情况
	 * @return array
	 */
	private function _dealMenuChildItem($item) {
		$result = array('type' => $item['type'], 'name' => $item['name']);
		switch($item['type']) {
			case 'view':
				$result['url'] = $this->dealUrl($item['url']);
				break;
			case 'click':
				$result['key'] = $item['click_key'];
				break;
		}
		return $result;
	}
	
	/**
	 * 处理URL地址
	 */
	public function dealUrl($url) {
		if(empty($this->host) || substr($url, 0, 4) == 'http') {
			return $url;
		}
		if(substr($url, 0, 1) == '/') {
			return $this->host.$url;
		}
		return $this->host.'/'.$url;
	}
	
	/**
	 * 设置HOST域名
	 */
	function setHost($host) {
		if(substr($host, -1) == '/') {
			$host = substr($host, 0, -1);
		}
		$this->host = $host;
	}
	
	public function getTypes() {
		return $this->_types;
	}
}