<?php
namespace Portal\Model;

use Common\Model\CommonModel;

class PostsModel extends CommonModel {
	/*
	 * 表结构
	 * id:post的自增id
	 * post_author:用户的id
	 * post_date:发布时间
	 * post_content
	 * post_title
	 * post_excerpt:发表内容的摘录
	 * post_status:发表的状态,可以有多个值,分别为publish->发布,delete->删除,...
	 * comment_status:
	 * post_password
	 * post_name
	 * post_modified:更新时间
	 * post_content_filtered
	 * post_parent:为父级的post_id,就是这个表里的ID,一般用于表示某个发表的自动保存，和相关媒体而设置
	 * post_type:可以为多个值,image->表示某个post的附件图片;audio->表示某个post的附件音频;video->表示某个post的附件视频;...
	 */
	//post_type,post_status注意变量定义格式;
	
	protected $_auto = array (
		array ('post_date', 'mGetDate', 1, 'callback' ), 	// 增加的时候调用回调函数
		//array ('post_modified', 'mGetDate', 2, 'callback' ) 
	);
	// 获取当前时间
	function mGetDate() {
		return date ( 'Y-m-d H:i:s' );
	}
	
	
	/**
	 * 通过关键词获取微信回复内容
	 * @param  string $keyword 关键词
	 * @return array  			消息回复内容
	 */
	function getWechatMsgByKeyword($keyword) {
		$where = array('post_keywords' => array('like', '%'.$keyword.'%'));
		$where['post_status'] = 1;
		$list = $this->where($where)->order('istop desc,recommended desc,post_hits desc,post_like desc')->limit(10)->getField('id,post_title as title,post_excerpt as summary,post_content as content,smeta');
		$result = array();
		if($list) {
			$items = array();
			foreach($list as $val) {
				$thumb = '';
				if($val['smeta']) {
					$val['smeta'] = json_decode($val['smeta'], true);
					if(isset($val['smeta']['thumb']) && $val['smeta']['thumb']) {
						$thumb = $val['smeta']['thumb'];
					} else if(isset($val['smeta']['photo']) && is_array($val['smeta']['photo']) && $val['smeta']['photo']) {
						$photo = array_shift($val['smeta']['photo']);
						if(isset($photo['url'])) {
							$thumb = $photo['url'];
						}
					}
				}
				$subtitle = $val['summary'];
				if(empty($subtitle)) {
					$subtitle = mb_substr(trim(strip_tags($val['content'])), 0, 120, 'UTF-8');
				}
				$items[] = array(
						'oid' => $val['id'],
						'from' => 'portal',
						'title' => $val['title'],
						'subtitle' => $subtitle,
						'thumb' => $thumb
					);
			}
			$result = array('id' => $items[0]['oid'], 'type' => 3, 'name' => $items[0]['title'], 'keywords' => $keyword);
			if(count($items) == 1) {
				$result['content'] = $items[0];
			} else {
				$result['content'] = $items;
			}
		}
		return $result;
	}

	protected function _before_write(&$data) {
		parent::_before_write($data);
	}
}