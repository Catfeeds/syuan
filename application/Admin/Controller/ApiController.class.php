<?php
namespace Admin\Controller;

use Common\Controller\AdminbaseController;

class ApiController extends AdminbaseController{

    function index() {
        $where = array();
        $name = I('name', '', 'trim');
        $method = I('method', '', 'trim');
        $path = I('path', '', 'trim');
        $gid = I('gid', 0, 'intval');
        if($name) {
            $where['name'] = array('like', '%'.$name.'%');
        }
        if($method) {
            $where['method'] = $method;
        }
        if($path) {
            $where['path'] = array('like', '%'.$path.'%');
        }
        if($gid > 0) {
            $where['gid'] = $gid;
        }
        $count = D('Common/Api')->where($where)->count();
        $page = $this->page($count, 20);
        $list = D('Common/Api')->where($where)->order('id desc')->limit($page->firstRow . ',' . $page->listRows)->select();

        $grouplist = M('AppApiGroup')->order('listorder asc')->getField('gid,name');

        $this->assign('name', $name);
        $this->assign('path', $path);
        $this->assign('method', $method);
        $this->assign('gid', $gid);
        $this->assign('grouplist', $grouplist);
        $this->assign('list', $list);
        $this->assign("Page", $page->show('Admin'));
        $this->display();
    }

    function doc() {
        $list = array();
        foreach(M('AppApiGroup')->order('listorder asc')->getField('gid,name') as $gid => $name) {
            $list[$gid] = array('name' => $name, 'child' => D('Common/Api')->where(array('gid' => $gid))->select());
        }
        foreach($list as $key => $item) {
            if($item['child']) {
                foreach($item['child'] as $k => $api) {
                    $api['params'] = D('Common/ApiParams')->where(array('api_id' => $api['id']))->order('listorder asc')->select();
                    $api['response'] = D('Common/ApiResponse')->getResponseWidthChild($api['id']);
                    $item['child'][$k] = $api;
                }
            }
            $list[$key] = $item;
        }
        $types = D('Common/Api')->getTypes();
        $this->assign('types', $types);
        $this->assign('list', $list);
        $this->display();
    }

    function add() {
        $grouplist = M('AppApiGroup')->order('gid desc')->getField('gid,name');
        $this->assign('grouplist', $grouplist);
        $this->display();
    }

    function add_post() {
        if(IS_POST) {
            if(D('Common/Api')->create()) {
                if(D('Common/Api')->add() !== false) {
                    $this->success("保存成功！");
                } else {
                    $this->error("添加失败！");
                }
            } else {
                $this->error(D('Common/Api')->getError());
            }
        }
    }

    function edit() {
        $id = I('id', 0, 'intval');
        $api = D('Common/Api')->find($id);
        if(!$api) {
            $this->error('API不存在!');
        }
        $grouplist = M('AppApiGroup')->order('gid desc')->getField('gid,name');
        $this->assign('grouplist', $grouplist);
        $this->assign('api', $api);
        $this->display();
    }

    function edit_post() {
        if(IS_POST) {
            $id = I('id', 0, 'intval');
            $data = D('Common/Api')->find($id);
            if(!$data) {
                $this->error('API不存在!');
            }
            if(D('Common/Api')->create()) {
                if(D('Common/Api')->save() !== false) {
                    $this->success("保存成功！");
                } else {
                    $this->error("修改失败！");
                }
            } else {
                $this->error(D('Common/Api')->getError());
            }
        }
    }

    function del() {
        $id = I('id', 0, 'intval');
        $api = D('Common/Api')->find($id);
        if(!$api) {
            $this->error('API不存在!');
        }
        if(D('Common/Api')->delete($id)) {
            D('Common/ApiParams')->delByApiId($id);
            D('Common/ApiResponse')->delByApiId($id);
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    function params() {
        $api_id = I('api_id', 0, 'intval');
        $api = D('Common/Api')->find($api_id);
        if(!$api) {
            $this->error('API不存在!');
        }

        $list = D('Common/ApiParams')->where(array('api_id' => $api_id))->order('listorder asc')->select();
        $types = D('Common/Api')->getTypes();

        $this->assign('types', $types);
        $this->assign('list', $list);
        $this->assign('api', $api);
        $this->display();
    }

    function params_listorder() {
        if(IS_POST) {
            if(parent::_listorders(D('Common/ApiParams'))) {
                $this->success("排序更新成功!");
            } else {
                $this->error("排序更新失败!");
            }
        }
    }

    function params_add() {
        $api_id = I('api_id', 0, 'intval');
        $api = D('Common/Api')->find($api_id);
        if(!$api) {
            $this->error('API不存在!');
        }
        $types = D('Common/Api')->getTypes();
        $this->assign('types', $types);
        $this->assign('api', $api);
        $this->display();
    }

    function params_add_post() {
        if(IS_POST) {            
            $api_id = I('api_id', 0, 'intval');
            $api = D('Common/Api')->find($api_id);
            if(!$api) {
                $this->error('API不存在!');
            }
            $types = D('Common/Api')->getTypes();
            $names = I('names', array());
            $introduces = I('introduces', array());
            $types = I('types', array());
            $musts = I('musts', array());
            $defaults = I('defaults', array());
            $listorders = I('listorders', array());
            if(empty($names)) {
                $this->error('请输入参数名称!');
            } else if(empty($introduces)) {
                $this->error('请输入参数说明!');
            } else {
                $list = array();
                foreach($names as $key => $name) {
                    $list[] = array(
                            'api_id' => $api_id,
                            'name' => trim($name),
                            'introduce' => trim($introduces[$key]),
                            'type' => intval($types[$key]),
                            'musts' => intval($musts[$key]),
                            'default' => trim($defaults[$key]),
                            'listorder' => intval($listorders[$key])
                        );
                }
                if(D('Common/ApiParams')->addAll($list)) {
                    $this->success('保存成功!');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    function params_edit() {
        $id = I('id', 0, 'intval');
        $param = D('Common/ApiParams')->find($id);
        if(!$param) {
            $this->error('数据不存在!');
        }

        $types = D('Common/Api')->getTypes();
        $this->assign('types', $types);
        $this->assign('param', $param);
        $this->display();
    }

    function params_edit_post() {
        if(IS_POST) {
            $id = I('id', 0, 'intval');
            $param = D('Common/ApiParams')->find($id);
            if(!$param) {
                $this->error('数据不存在!');
            }
            $data = array(
                'id' => $id,
                'name' => I('name', '', 'trim'),
                'introduce' => I('introduce', '', 'trim'),
                'must' => I('must', 0, 'intval'),
                'default' => I('default', '', 'trim'),
                'type' => I('type', 0, 'intval'),
                'listorder' => I('listorder', 0, 'intval')
            );
            if(empty($data['name'])) {
                $this->error('请输入参数名称!');
            } else if(empty($data['introduce'])) {
                $this->error('请输入参数说明!');
            } else {
                if(D('Common/ApiParams')->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error("保存失败");
                }
            }
        }
    }

    function params_del() {
        $id = I('id', 0, 'intval');
        $param = D('Common/ApiParams')->find($id);
        if(!$param) {
            $this->error('数据不存在!');
        }
        if(D('Common/ApiParams')->delete($id)) {
            $this->success('删除成功');
        } else {
            $this->error("删除失败");
        }
    }

    function response() {
        $api_id = I('api_id', 0, 'intval');
        $api = D('Common/Api')->find($api_id);
        if(!$api) {
            $this->error('API不存在!');
        }
        
        $result = D('Common/ApiResponse')->where(array('api_id' => $api_id))->order('listorder asc')->select();
        import("Tree");
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        
        $list =array();
        foreach ($result as $m){
            $list[$m['id']] = $m; 
        }
        $types = D('Common/Api')->getTypes();
        foreach ($result as $n => $r) {
            $result[$n]['typename'] = $types[$r['type']];
            $result[$n]['level'] = $this->_get_level($r['id'], $list);
            $result[$n]['parentid_node'] = ($r['parentid']) ? ' class="child-of-node-' . $r['parentid'] . '"' : '';
            $result[$n]['str_manage'] = '';
            if(in_array($r['type'], array(8, 9))) {
                $result[$n]['str_manage'] = '<a href="'.U('Api/response_add', array('api_id' => $api_id, 'parentid' => $r['id'])).'">添加下级参数</a> |'; 
            }
            $result[$n]['str_manage'] .= '<a href="'.U("Api/response_edit", array("id" => $r['id'])).'">编辑</a> | <a class="js-ajax-delete" href="'.U("Api/response_del", array("id" => $r['id'])).'">'.L('DELETE').'</a>';
        }

        $tree->init($result);
        $str = "<tr id='node-\$id' \$parentid_node>
                    <td style='padding-left:20px;'><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
                    <td>\$spacer\$name</td>
                    <td>\$typename</td>
                    <td>\$introduce</td>
                    <td>\$str_manage</td>
                </tr>";
        $categorys = $tree->get_tree(0, $str);
        $this->assign("categorys", $categorys);
        $this->assign('api', $api);
        $this->display();
    }

    /**
     * 获取深度
     * @param $id
     * @param $array
     * @param $i
     */
    protected function _get_level($id, $array = array(), $i = 0) {
    
        if ($array[$id]['parentid']==0 || empty($array[$array[$id]['parentid']]) || $array[$id]['parentid']==$id){
            return  $i;
        }else{
            $i++;
            return $this->_get_level($array[$id]['parentid'],$array,$i);
        }
    
    }

    function response_listorder() {
        if(IS_POST) {
            if(parent::_listorders(D('Common/ApiResponse'))) {
                $this->success("排序更新成功!");
            } else {
                $this->error("排序更新失败!");
            }
        }
    }

    function response_add() {
        $api_id = I('api_id', 0, 'intval');
        $api = D('Common/Api')->find($api_id);
        if(!$api) {
            $this->error('API不存在!');
        }
        $parentid = I('parentid', 0, 'intval');
        if($parentid > 0) {
            $parent = D('Common/ApiResponse')->find($parentid);
            if(!$parent) {
                $this->error('上级数据项不存在!');
            }
        }
        $types = D('Common/Api')->getTypes();
        $this->assign('types', $types);
        $this->assign('api', $api);
        $this->assign('parentid', $parentid);
        $this->display();
    }

    function response_add_post() {
        if(IS_POST) {            
            $api_id = I('api_id', 0, 'intval');
            $api = D('Common/Api')->find($api_id);
            if(!$api) {
                $this->error('API不存在!');
            }
            $parentid = I('parentid', 0, 'intval');
            if($parentid > 0) {
                $parent = D('Common/ApiResponse')->find($parentid);
                if(!$parent) {
                    $this->error('上级数据项不存在!');
                }
            }
            $types = D('Common/Api')->getTypes();
            $names = I('names', array());
            $types = I('types', array());
            $introduces = I('introduces', array());
            $listorders = I('listorders', array());
            if(empty($names)) {
                $this->error('请输入参数名称!');
            } else if(empty($introduces)) {
                $this->error('请输入参数说明!');
            } else {
                $list = array();
                foreach($names as $key => $name) {
                    $list[] = array(
                            'api_id' => $api_id,
                            'parentid' => $parentid,
                            'name' => trim($name),
                            'introduce' => trim($introduces[$key]),
                            'type' => intval($types[$key]),
                            'listorder' => intval($listorders[$key])
                        );
                }
                if(D('Common/ApiResponse')->addAll($list)) {
                    $this->success('保存成功!');
                } else {
                    $this->error('保存失败');
                }
            }
        }
    }

    function response_edit() {
        $id = I('id', 0, 'intval');
        $response = D('Common/ApiResponse')->find($id);
        if(!$response) {
            $this->error('数据不存在!');
        }
        $types = D('Common/Api')->getTypes();
        $this->assign('types', $types);
        $this->assign('response', $response);
        $this->display();
    }

    function response_edit_post() {
        if(IS_POST) {
            $id = I('id', 0, 'intval');
            $response = D('Common/ApiResponse')->find($id);
            if(!$response) {
                $this->error('数据不存在!');
            }
            $data = array(
                'id' => $id,
                'name' => I('name', '', 'trim'),
                'type' => I('type', 0, 'intval'),
                'introduce' => I('introduce', '', 'trim'),
                'listorder' => I('listorder', 0, 'intval')
            );
            if(empty($data['name'])) {
                $this->error('请输入参数名称!');
            } else if(empty($data['introduce'])) {
                $this->error('请输入参数说明!');
            } else {
                if(D('Common/ApiResponse')->save($data)) {
                    $this->success('保存成功');
                } else {
                    $this->error("保存失败");
                }
            }
        }
    }

    function response_del() {
        $id = I('id', 0, 'intval');
        $response = D('Common/ApiResponse')->find($id);
        if(!$response) {
            $this->error('数据不存在!');
        }
        if(D('Common/ApiResponse')->delById($id)) {
            $this->success('删除成功');
        } else {
            $this->error("删除失败");
        }
    }
}
