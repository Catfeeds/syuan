<?php
namespace Wechat\Model;
use Common\Model\CommonModel;

class WxAutoReplyModel extends CommonModel {
	
	protected $tableName = 'wechat_auto_reply';
	
	//自动回复消息类型
	protected $_types = array(1 => '文本', 2 => '图片', 3 => '图文', 4 => '多图文');
	
	//自动验证
	protected $_validate = array(
			//array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
			array('original_id', 'require', '请选择当前公共号！', 0),
			array('name', 'require', '规则名称不能为空！', 1),
			array('keywords', 'require', '关键词必须填写', 1),
			array('type', array(1, 2, 3, 4), '消息类型错误！', 1, 'in')
	);
	
	//自动完成
	protected $_auto = array (
		array('keywords', 'delKeywords', 3, 'callback'),
        array('create_at', 'getDateTime', 1, 'callback')
	);
	
	/**
	 * 根据关键词查账自动回复内容
	 * @param  string $original_id 原始ID
	 * @param  string $keyword     关键词
	 * @return string              
	 */
	public function getReplyByKeyword($original_id, $keyword) {
		$nowdatetime = date('Y-m-d H:i:s');
		$where = array('original_id' => $original_id);
		$where['keywords'] = array('like', '%'.$keyword.'%');
		$where['expire_start'] = array('lt', $nowdatetime); 
		$where['expire_at'] = array('gt', $nowdatetime);
		$list = $this->where($where)->order('type desc')->limit(10)->getField('id,type,name,keywords,content');
		$result = array();
		if($list) {
			$items = array();
			foreach($list as $key => $val) {
				$items[$val['type']][] = $this->decode($val);
			}
			if(isset($items['4'])) {//多图文
				$result = array_shift($items['4']);
			} else if(isset($items['3'])) {
				$result = $items[3][0];
				if(count($items['3']) > 1) {//组装多图文
					$result['type'] = 4;
					$result['content'] = array();
					foreach ($items['3'] as $value) {
						$result['content'][] = $value['content'];
					}
				}
			} else if(isset($items['2'])) {//图片
				$result = array_shift($items['2']);
			} else if(isset($items['1'])) {//文本
				$result = array_shift($items['1']);
			}
		}
		return $result;
	}

	//自动回复处理
	public function decode($message) {
		if(in_array($message['type'], array(3, 4))) {
			$message['content'] = unserialize($message['content']);
		}
		return $message;
	}
	
	//获取消息类型
	public function getTypes() {
		return $this->_types;
	}
	
	//处理输入关键字
	public function delKeywords($keywords) {
		$keywords = explode("\r\n", $keywords);
		$keywords = array_unique($keywords);
		return implode(' ', $keywords);
	}
}