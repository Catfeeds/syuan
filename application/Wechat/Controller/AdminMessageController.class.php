<?php

namespace Wechat\Controller;

use Common\Controller\AdminbasewechatController;

class AdminMessageController extends AdminbasewechatController {

	protected $replay_model;
	
	function _initialize() {
		parent::_initialize();
		$this->replay_model = D("Wechat/WxAutoReply");
	}
	
	/**
	 * 自动回复列表
	 */
	function index() {
		C('TOKEN_ON', false);
		$types = $this->replay_model->getTypes();
		$where = array('original_id' => $this->wechatmp['original_id']);
		$type = I('get.type', 0,'intval');
		$name = I('get.name', '', 'trim');
		$keywords = I('get.keywords', '', 'trim');
		if($type > 0) {
			$where['type'] = array('eq', $type);
		}
		if($name) {
			$where['name'] = array('like', '%'.$name.'%');
		}
		if($keywords) {
			$where['keywords'] = array('like', '%'.$keywords.'%');
		}
		$count=$this->replay_model->where($where)->count();
		$page = $this->page($count, 20);
		$list = $this->replay_model->where($where)->order("id DESC")->limit($page->firstRow . ',' . $page->listRows)->select();
		foreach($list as $key => $val) {
			$val = $this->replay_model->decode($val);
			$val['keywords'] = str_replace(' ', "<br/>", $val['keywords']);
			$list[$key] = $val;
		}
		$this->assign("formget", array('type' => $type, 'name' => $name, 'keywords' => $keywords));
		$this->assign('list', $list);
		$this->assign('types', $types);
		$this->assign("Page", $page->show('Admin'));
		$this->display();
	}
	
	/**
	 * 添加
	 */
	function add() {
		$this->display();
	}
	
	/**
	 * 提交添加
	 */
	function add_post() {
		if(IS_POST) {
			if ($this->replay_model->create()) {
				$this->replay_model->original_id = $this->wechatmp['original_id'];
				switch($this->replay_model->type) {
					case 2:
						$this->replay_model->content = I('post.replyimg', '', 'trim');
						if($this->replay_model->content) {
							$this->replay_model->content = sp_asset_relative_url($this->replay_model->content);
						}
						break;
					case 3:
					case 4:
						$data = array();
						$oids = I('post.oid');
						$froms = I('post.from');
						$titles = I('post.title');
						$subtitles = I('post.subtitle');
						$thumbs = I('post.thumb');
						foreach($oids as $k => $oid) {
							$data[] = array(
								'oid' => $oid,
								'from' => $froms[$k],
								'title' => $titles[$k],
								'subtitle' => $subtitles[$k],
								'thumb' => sp_asset_relative_url($thumbs[$k])
							);
						}
						if($this->replay_model->type == 3) {
							$data = array_shift($data);
						}
						$this->replay_model->content = serialize($data);
						break;
				}
				
				$mpid = $this->replay_model->add();
				if ($mpid !== false) {
					if($this->replay_model->count() == 1) {

					}
					$this->success("添加成功！");
				} else {
    				$this->error("添加失败！");
    			}
			} else {
    			$this->error($this->replay_model->getError());
    		}
		}
	}
	
	/**
	 * 修改
	 */
	function edit() {
		$id = intval(I("get.id"));
		$message = $this->replay_model->where("original_id = '{$this->wechatmp['original_id']}' and id=$id")->find();
		if (!$message) {
			$this->error("回复信息不存在 !");
		}
		$message = $this->replay_model->decode($message);
		$message['keywords'] = implode("\r\n", explode(' ', $message['keywords']));
		$this->assign('message', $message);
		$this->display();
	}
	
	/**
	 * 提交修改
	 */
	function edit_post() {
		if (IS_POST) {
			if ($this->replay_model->create()) {
				switch($this->replay_model->type) {
					case 2:
						$this->replay_model->content = I('post.replyimg', '', 'trim');
						if($this->replay_model->content) {
							$this->replay_model->content = sp_asset_relative_url($this->replay_model->content);
						}
						break;
					case 3:
					case 4:
						$data = array();
						$oids = I('post.oid');
						$froms = I('post.from');
						$titles = I('post.title');
						$subtitles = I('post.subtitle');
						$thumbs = I('post.thumb');
						foreach($oids as $k => $oid) {
							$data[] = array(
								'oid' => $oid,
								'from' => $froms[$k],
								'title' => $titles[$k],
								'subtitle' => $subtitles[$k],
								'thumb' => sp_asset_relative_url($thumbs[$k])
							);
						}
						if($this->replay_model->type == 3) {
							$data = array_shift($data);
						}
						$this->replay_model->content = serialize($data);
						break;
				}
				
				if ($this->replay_model->save()!==false) {
					$this->success("保存成功！");
				} else {
					$this->error("保存失败！");
				}
			} else {
    			$this->error($this->replay_model->getError());
    		}
		}
	}
	
	/**
	 * 关注时自动回复
	 */
	function subscribe() {
		$message = $this->wechatmp['reply_subscribe'];
		if(IS_POST) {
			$type = I('post.type', '', 'intval');
			$content = I('post.content', '', 'trim');
			switch($type) {
				case 2:
					$content = I('post.replyimg', '', 'trim');
					if($content) {
						$content = sp_asset_relative_url($content);
					}
					break;
				case 3:
				case 4:
					$data = array();
					$oids = I('post.oid');
					$froms = I('post.from');
					$titles = I('post.title');
					$subtitles = I('post.subtitle');
					$thumbs = I('post.thumb');
					foreach($oids as $k => $oid) {
						$data[] = array(
							'oid' => $oid,
							'from' => $froms[$k],
							'title' => $titles[$k],
							'subtitle' => $subtitles[$k],
							'thumb' => sp_asset_relative_url($thumbs[$k])
						);
					}
					if($type == 3) {
						$data = array_shift($data);
					}
					$content = $data;
					break;
			}
			$message = serialize(array('type' => $type, 'content' => $content));
			if($this->wxmp_model->where(array('original_id'=>$this->wechatmp['original_id']))->save(array('reply_subscribe' => $message))) {
				$this->success('保存成功');
			} else {
				$this->error('保存失败');
			}
		} else {
			if($message) {
				$message = unserialize($message);
			} else {
				$message = array('type' => 1, 'content' => '');
			}
		}
		$this->assign('message', $message);
		$this->assign('action', 'subscribe');
		$this->display('reply');
	}

	/**
	 * 回答不上来时自动回复
	 */
	function noanswer() {
		$message = $this->wechatmp['reply_noanswer'];
		if(IS_POST) {
			$type = I('post.type', '', 'intval');
			$content = I('post.content', '', 'trim');
			switch($type) {
				case 2:
					$content = I('post.replyimg', '', 'trim');
					if($content) {
						$content = sp_asset_relative_url($content);
					}
					break;
				case 3:
				case 4:
					$data = array();
					$oids = I('post.oid');
					$froms = I('post.from');
					$titles = I('post.title');
					$subtitles = I('post.subtitle');
					$thumbs = I('post.thumb');
					foreach($oids as $k => $oid) {
						$data[] = array(
							'oid' => $oid,
							'from' => $froms[$k],
							'title' => $titles[$k],
							'subtitle' => $subtitles[$k],
							'thumb' => sp_asset_relative_url($thumbs[$k])
						);
					}
					if($type == 3) {
						$data = array_shift($data);
					}
					$content = $data;
					break;
			}
			$message = serialize(array('type' => $type, 'content' => $content));
			if($this->wxmp_model->where(array('original_id'=>$this->wechatmp['original_id']))->save(array('reply_noanswer' => $message))) {
				$this->success('保存成功');
			} else {
				$this->error('保存失败');
			}
		} else {
			if($message) {
				$message = unserialize($message);
			} else {
				$message = array('type' => 1, 'content' => '');
			}
		}
		$this->assign('message', $message);
		$this->assign('action', 'noanswer');
		$this->display('reply');
	}

	/**
	 * 删除
	 */
	function delete() {
		if(isset($_GET['id'])){
			$id = intval(I("get.id"));
			$data['status']=0;
			if ($this->replay_model->where(array('original_id' => $this->wechatmp['original_id']))->delete($id)) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
		if(isset($_POST['ids'])){
			$ids = join(",", $_POST['ids']);
			if ($this->replay_model->where(array('original_id' => $this->wechatmp['original_id']))->delete($ids)) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
}