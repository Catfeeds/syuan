<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class AppController extends AdminbaseController{

    function index() {

        $list = D('Common/App')->order('app_id asc')->select();
        foreach($list as $key => $val) {
            $val['version'] = '尚未发布'; 
            $version = D('Common/Appupgrade')->where(array('app_id' => $val['app_id']))->order('version_code desc')->getField('version_code');
            if($version) {
                $val['version'] = $version;
            }
            $list[$key] = $val; 
        }
        $types = D('Common/App')->getTypes();

        $this->assign('types', $types);
        $this->assign('list', $list);
        $this->display();
    }

    function add() {
        $types = D('Common/App')->getTypes();
        $this->assign('types', $types);
		$this->assign('encryptionkey', sp_random_string(32));
        $this->display();
    }

    function add_post() {
        if(IS_POST) {
            $data = array(
                'type' => I('type', 0, 'intval'),
                'name' => I('name', '', 'trim'),
                'small_appid' => I('small_appid', '', 'trim'),
                'small_appsecret' => I('small_appsecret', '', 'trim'),
                'introduce' => I('introduce', '', 'trim'),
                'encryption' => I('encryption', 0, 'intval'),
                'apilog' => I('apilog', 0, 'intval'),
                'key' => I('key', '', 'trim'),
                'apk_url' => I('apk_url', '', 'trim'),
                'status' => I('status', 0, 'intval'),
            );
            $types = D('Common/App')->getTypes();
            if(empty($data['name'])) {
                $this->error('请填写APP名称');
            } else if(!isset($types[$data['type']])) {
                $this->error('APP类型错误');
            } else if($data['type'] == 5 && empty($data['small_appid'])) {
                $this->error('请填写小程序APPID');
            } else if($data['type'] == 5 && empty($data['small_appsecret'])) {
                $this->error('请填写小程序Secret');
            } else if(empty($data['introduce'])) {
                $this->error('请填写APP介绍');
            } else if(!in_array($data['encryption'], array(0, 1), true)) {
                $this->error('是否加密参数错误');
            } else if($data['encryption'] == 1 && empty($data['key'])) {
                $this->error('加密KEY不能为空');
            } else if(!in_array($data['status'], array(0, 1), true)) {
                $this->error('APP状态错误');
            } else {
                $data['create_at'] = date('Y-m-d H:i:s', TIMESTAMP);
                $data['update_at'] = date('Y-m-d H:i:s', TIMESTAMP);
                if(D('Common/App')->add($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('添加错误');
                }
            }
        }
    }

    function edit() {
        $app_id = I('app_id', 0, 'intval');
        $app = D('Common/App')->find($app_id);
        if(!$app) {
            $this->error('APP不存在!');
        }
        $types = D('Common/App')->getTypes();
        $this->assign('types', $types);
        $this->assign('app', $app);
        $this->display();
    }

    function edit_post() {
        if(IS_POST) {
            $app_id = I('app_id', 0, 'intval');
            $app = D('Common/App')->find($app_id);
            if(!$app) {
                $this->error('APP不存在!');
            }
            $data = array(
                'type' => I('type', 0, 'intval'),
                'name' => I('name', '', 'trim'),
                'small_appid' => I('small_appid', '', 'trim'),
                'small_appsecret' => I('small_appsecret', '', 'trim'),
                'introduce' => I('introduce', '', 'trim'),
                'encryption' => I('encryption', 0, 'intval'),
                'apilog' => I('apilog', 0, 'intval'),
                'key' => I('key', '', 'trim'),
                'apk_url' => I('apk_url', '', 'trim'),
                'status' => I('status', 0, 'intval'),
            );
            $types = D('Common/App')->getTypes();
            if(empty($data['name'])) {
                $this->error('请填写APP名称');
            } else if(!isset($types[$data['type']])) {
                $this->error('APP类型错误');
            } else if($data['type'] == 5 && empty($data['small_appid'])) {
                $this->error('请填写小程序APPID');
            } else if($data['type'] == 5 && empty($data['small_appsecret'])) {
                $this->error('请填写小程序Secret');
            } else if(empty($data['introduce'])) {
                $this->error('请填写APP介绍');
            } else if(!in_array($data['encryption'], array(0, 1), true)) {
                $this->error('是否加密参数错误');
            } else if($data['encryption'] == 1 && empty($data['key'])) {
                $this->error('加密KEY不能为空');
            } else if(!in_array($data['status'], array(0, 1), true)) {
                $this->error('APP状态错误');
            } else {
                $data['update_at'] = date('Y-m-d H:i:s', TIMESTAMP);
                if(D('Common/App')->where(array('app_id' => $app_id))->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error('修改错误');
                }
            }
        }
    }

    function del() {
        $app_id = I('app_id', 0, 'intval');
        $app = D('Common/App')->find($app_id);
        if(!$app) {
            $this->error('APP不存在!');
        }
        if(D('Common/App')->delete($app_id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    function upgrade() {
        $app_id = I('app_id', 0, 'intval');
        $applist = D('Common/App')->order('app_id asc')->getField('app_id, name, type');
        $types = D('Common/App')->getTypes();
        $status_map = D('Common/Appupgrade')->getStatusMap();
        $where = array();
        if($app_id > 0) {
            $where['app_id'] = $app_id;
        }
        $list = D('Common/Appupgrade')->where($where)->order('id desc')->select();

        $this->assign('types', $types);
        $this->assign('status_map', $status_map);
        $this->assign('applist', $applist);
        $this->assign('list', $list);
        $this->display();
    }

    function upgrade_add() {

        $app_id = I('app_id', 0, 'intval');
        $applist = D('Common/App')->order('app_id asc')->getField('app_id, name, type');
        $types = D('Common/App')->getTypes();
        $status_map = D('Common/Appupgrade')->getStatusMap();

        $this->assign('app_id', $app_id);
        $this->assign('types', $types);
        $this->assign('status_map', $status_map);
        $this->assign('applist', $applist);
        $this->display();
    }

    function upgrade_add_post() {
        if(IS_POST) {
            $app_id = I('app_id', 0, 'intval');
            $app = D('Common/App')->find($app_id);
            if(!$app) {
                $this->error('APP不存在!');
            }
            if(D('Common/Appupgrade')->create()) {
                if(D('Common/Appupgrade')->add() !== false) {
                    $this->success("保存成功！");
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error(D('Common/Appupgrade')->getError());
            }
        }
    }

    function upgrade_edit() {
        $id = I('id', 0, 'intval');
        $info = D('Common/Appupgrade')->find($id);
        if(!$info) {
            $this->error('APP版本不存在!');
        }

        $applist = D('Common/App')->order('app_id asc')->getField('app_id, name, type');
        $types = D('Common/App')->getTypes();
        $status_map = D('Common/Appupgrade')->getStatusMap();

        $this->assign('types', $types);
        $this->assign('status_map', $status_map);
        $this->assign('applist', $applist);
        $this->assign('info', $info);
        $this->display();
    }

    function upgrade_edit_post() {
        if(IS_POST) {
            $id = I('id', 0, 'intval');
            $data = D('Common/Appupgrade')->find($id);
            if(!$data) {
                $this->error('APP版本不存在!');
            }
            if(D('Common/Appupgrade')->create()) {
                if(D('Common/Appupgrade')->save() !== false) {
                    $this->success("保存成功！");
                } else {
                    $this->error("修改失败！");
                }
            } else {
                $this->error(D('Common/Appupgrade')->getError());
            }
        }
    }

    function upgrade_del() {
        $id = I('id', 0, 'intval');
        $data = D('Common/Appupgrade')->find($id);
        if(!$data) {
            $this->error('APP版本不存在!');
        }
        if(D('Common/Appupgrade')->delete($id)) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}
