<?php

namespace Wechat\Controller;

use Common\Controller\AdminbasewechatController;
use Wechat\Logic\WechatLogic;

class AdminMenuController extends AdminbasewechatController {
	
	protected $menu_types = array();
	
	function _initialize() {
		parent::_initialize();
		$options = get_site_options();
		$this->wxmenu_model = D("Wechat/WxMenu");
		$this->wxmenu_model->setHost($options['site_host']);
		$this->menu_types = $this->wxmenu_model->getTypes();
	}
	
	/**
	 * 自定义菜单列表
	 */
	function index(){

		$result = $this->wxmenu_model->where(array('original_id' => $this->wechatmp['original_id'], 'catid' => 0))
			->order(array("listorder"=>"asc"))->select();
		
		$tree = new \Tree();
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		foreach ($result as $r) {
			$r['str_manage'] = '<a href="javascript:open_iframe_dialog(\''.U('AdminMenu/edit', array("id" => $r['id'])).'\', \'添加菜单\', {width:\'800px\',height:\'450px\'})">'.L('EDIT').'</a> | <a class="js-ajax-delete" href="' . U("AdminMenu/delete", array("id" => $r['id'])) . '">'.L('DELETE').'</a> ';
			$r['typename'] = '一级菜单';
			if($r['type']) {
				$r['typename'] = $this->menu_types[$r['type']];
			}
			
			switch($r['type']) {
				case 'view':
					$r['content'] = '<a href="'.$this->wxmenu_model->dealUrl($r['url']).'" target="_blank">'.$r['url'].'</a>';
					break;
				case 'click':
					$r['content'] = $r['click_key'];
			}
			
			/*判断一级菜单是否有子菜单*/
			if($r['parentid'] == 0) {
				$childcount = $this->wxmenu_model->where(array(
					'original_id' => $this->wechatmp['original_id'], 
					'parentid' => $r['id']
					)
				)->count();
				if($childcount > 0) {
					$r['typename'] = '一级菜单';
					$r['content'] = '';
				}
			}
			
			$r['status'] = $r['status'] ? '<i class="fa fa-check green" aria-hidden="true"></i>' : '<i class="fa fa-times red" aria-hidden="true"></i>';
			$array[$r['id']] = $r;
		}
		
		$tree->init($array);
		$str = "<tr>
					<td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
					<td>\$spacer\$name</td>
					<td>\$typename</td>
	    			<td>\$content</td>
					<td>\$status</td>
					<td>\$str_manage</td>
				</tr>";
		$this->assign("taxonomys", $tree->get_tree(0, $str));
		$this->display();
	}
	
	/**
	 * 添加菜单
	 */
	function add() {
		$parent = $this->wxmenu_model->where(array('original_id' => $this->wechatmp['original_id'], 'parentid' => 0, 'catid' => 0))
			->order(array("listorder"=>"asc"))->getField('id,name');
		$this->assign('parent', $parent);
		$this->assign('types', $this->menu_types);
		$this->display();
	}
	
	/**
	 * 提交添加
	 */
	function add_post() {
		if (IS_POST) {
			if ($this->wxmenu_model->create()) {
				$this->wxmenu_model->original_id = $this->wechatmp['original_id'];
				$content = I('post.content', '', 'trim');
				switch($this->wxmenu_model->type) {
					case 'view':
						$this->wxmenu_model->url = $content;
						break;
					case 'click':
						$this->wxmenu_model->click_key = $content;
						break;
				}
				$id = $this->wxmenu_model->add();
				if ($id !== false) {
					$this->success("添加成功！");
				} else {
    				$this->error("添加失败！");
    			}
			} else {
    			$this->error($this->wxmenu_model->getError());
    		}
		}
	}
	
	/**
	 * 修改
	 */
	function edit() {
		$id = intval(I("get.id"));
		$menu = $this->wxmenu_model->where(array('original_id' => $this->wechatmp['original_id'], 'id' => $id))->find();
		if (!$menu) {
			$this->error("菜单不存在！");
		}
		switch($menu['type']) {
			case 'view':
				$menu['content'] = $menu['url'];
				break;
			case 'click':
				$menu['content'] = $menu['click_key'];
				break;
		}
		$parent = $this->wxmenu_model->where(array('original_id' => $this->wechatmp['original_id'], 'parentid' => 0, 'catid' => 0))
			->order(array("listorder"=>"asc"))->getField('id,name');
		$this->assign('parent', $parent);
		$this->assign('types', $this->menu_types);
		$this->assign('menu', $menu);
		$this->display();
	}
	
	/**
	 * 提交修改
	 */
	function edit_post() {
		if (IS_POST) {
			$parentid = intval(I("post.parentid"));
			$id = intval(I("post.id"));
			$where = array(
						'original_id' => $this->wechatmp['original_id'],
						'parentid' => $id
						);
			if($parentid > 0 && $this->wxmenu_model->where($where)->count() > 0) {
				$this->error("该菜单下含有子菜单不能设置为二级菜单!");
			} else {
				if ($this->wxmenu_model->create()) {
					$content = I('post.content', '', 'trim');
					switch($this->wxmenu_model->type) {
						case 'view':
							$this->wxmenu_model->url = $content;
							break;
						case 'click':
							$this->wxmenu_model->click_key = $content;
							break;
					}
					if ($this->wxmenu_model->save()!==false) {
						$this->success("保存成功！");
					} else {
						$this->error("保存失败！");
					}
				} else {
					$this->error($this->wxmenu_model->getError());
				}

			}
		}
	}
	
	//排序
	public function listorders() {
		$status = parent::_listorders($this->wxmenu_model);
		if ($status) {
			$this->success("排序更新成功！");
		} else {
			$this->error("排序更新失败！");
		}
	}
	
	/**
	 * 菜单删除
	 */
	function delete() {
		$id = intval(I("get.id"));
		$where = array(
					'original_id' => $this->wechatmp['original_id'],
					'parentid' => $id
					);
		if($this->wxmenu_model->where($where)->count() > 0) {
			$this->error("该菜单下含有子菜单不能删除!");
		} else {
			if ($this->wxmenu_model->where(array(
					'original_id' => $this->wechatmp['original_id'],
					'id' => $id
					))->delete()!==false) {
				$this->success("删除成功！");
			} else {
				$this->error("删除失败！");
			}
		}
	}
	
	//生成自定义菜单
	function makemenu() {
		
		$buttons = $this->wxmenu_model->getWxMenu($this->wechatmp['original_id'], 0);		
		if(!$buttons) {
			$this->error("请先添加菜单");
		} else {
			$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
			try {
				$result = $wechat->getApi()->menu->add($buttons);
			} catch (\Exception $e) {
				$this->error('生成菜单失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
			}
			if($result['errcode'] == 0) {
				$this->success('生成菜单成功!', U('Wechat/AdminMenu/index'));
			} else {
				$this->error('生成菜单失败, 错误信息: code:'.$result['errcode'].', msg:'.$result['errmsg']);
			}
		}
	}
	
	//删除自定义菜单
	function delmenu() {
		$wechat = D('Wechat/Wechat', 'Logic')->init($this->wechatmp);
		try {
			$result = $wechat->getApi()->menu->destroy();
		} catch (\Exception $e) {
			$this->error('删除菜单失败, 错误信息: code:'.$e->getCode().', msg:'.$e->getMessage());
		}
		if($result['errcode'] == 0) {
			$this->success('删除菜单成功!', U('Wechat/AdminMenu/index'));
		} else {
			$this->error('删除菜单失败, 错误信息: code:'.$result['errcode'].', msg:'.$result['errmsg']);
		}
	}
}