<?php

/**
 * 后台首页
 */
namespace Admin\Controller;
use Common\Controller\AdminbaseController;
class IndexController extends AdminbaseController {
	
	function _initialize() {
	    empty($_GET['upw'])?"":session("__SP_UPW__",$_GET['upw']);//设置后台登录加密码	    
		parent::_initialize();
		$this->initMenu();
	}
	
    /**
     * 后台框架首页
     */
    public function index() {
    	if (C('LANG_SWITCH_ON',null,false)){
    		$this->load_menu_lang();
    	}
		$selectmpid = I('get.mpid', 0, 'intval');
		if($selectmpid > 0) {
			session('WECHAT_MPID', $selectmpid);
		}
		$mpid = get_current_wechat_mpid();
		$mplist = D("Wechat/WxMp")->getField('mpid,original_id,wechat_account,name');
		if(count($mplist) == 1 && (!isset($mplist[$mpid]) || $mpid < 1)) {
			foreach($mplist as $id => $val) {
				session('WECHAT_MPID', $id);
				break;
			}
		}
        if($this->ismobile) {
            $parentid = I('parentid', 0, 'intval');
            $menulist = D("Common/Menu")->admin_menu($parentid);
            foreach($menulist as $key => $val) {
                $menulist[$key]['child'] = D("Common/Menu")->admin_menu($val['id']);
                if($menulist[$key]['child']) {
                    $menulist[$key]['url'] = U('Admin/Index/index', array('parentid' => $val['id']));
                } else {
                    $menulist[$key]['url'] = U($val['app'].'/'.$val['model'].'/'.$val['action']);
                }
                if(!$val['icon']) {
                    $menulist[$key]['icon'] = 'file';
                }
            }
            $this->assign('menulist', $menulist);
        }
		$this->assign("mpid", $mpid);
		$this->assign("mplist", $mplist);
        $this->assign("SUBMENU_CONFIG", D("Common/Menu")->menu_json());
       	$this->display();
        
    }
    
    private function load_menu_lang(){
    	$apps=sp_scan_dir(SPAPP."*",GLOB_ONLYDIR);
    	$error_menus=array();
    	foreach ($apps as $app){
    		if(is_dir(SPAPP.$app)){
    			$admin_menu_lang_file=SPAPP.$app."/Lang/".LANG_SET."/admin_menu.php";
    			if(is_file($admin_menu_lang_file)){
    				$lang=include $admin_menu_lang_file;
    				L($lang);
    			}
    		}
    	}
    }

}

