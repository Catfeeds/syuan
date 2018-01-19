<?php
namespace Common\Controller;
use Think\Controller;

class AppframeController extends Controller {

	protected $cache_tail = '_tail';
    function _initialize() {
		$this->cache_tail = md5(C('DB_HOST').C('DB_NAME').C('DB_PREFIX'));
        $this->assign('waitSecond', 3);
        $this->assign('js_debug', APP_DEBUG ? '?v='.TIMESTAMP : '');
        if(APP_DEBUG) {

        }
        $options = sp_get_site_options();        
        $this->assign($options);
        $host = substr(UU('/', array(), true, true), 0, -1);
        $this->assign('host', $host);
    }

    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data, $type = '',$json_option=0) {
        
        $data['referer']=$data['url'] ? $data['url'] : "";
        $data['state']=$data['status'] ? "success" : "fail";
        
        if(empty($type)) $type  =   C('DEFAULT_AJAX_RETURN');
        switch (strtoupper($type)){
        	case 'JSON' :
        		// 返回JSON数据格式到客户端 包含状态信息
        		header('Content-Type:application/json; charset=utf-8');
        		exit(json_encode($data,$json_option));
        	case 'XML'  :
        		// 返回xml格式数据
        		header('Content-Type:text/xml; charset=utf-8');
        		exit(xml_encode($data));
        	case 'JSONP':
        		// 返回JSON数据格式到客户端 包含状态信息
        		header('Content-Type:application/json; charset=utf-8');
        		$handler  =   isset($_GET[C('VAR_JSONP_HANDLER')]) ? $_GET[C('VAR_JSONP_HANDLER')] : C('DEFAULT_JSONP_HANDLER');
        		exit($handler.'('.json_encode($data,$json_option).');');
        	case 'EVAL' :
        		// 返回可执行的js脚本
        		header('Content-Type:text/html; charset=utf-8');
        		exit($data);
        	case 'AJAX_UPLOAD':
        		// 返回JSON数据格式到客户端 包含状态信息
        		header('Content-Type:text/html; charset=utf-8');
        		exit(json_encode($data,$json_option));
        	default :
        		// 用于扩展其他返回格式数据
        		Hook::listen('ajax_return',$data);
        }
        
    }
    
    //分页
    protected function page($Total_Size = 1, $Page_Size = 0, $Current_Page = 1, $listRows = 6, $PageParam = '', $PageLink = '', $Static = FALSE) {
    	import('Page');
    	if ($Page_Size == 0) {
    		$Page_Size = C("PAGE_LISTROWS");
    	}
    	if (empty($PageParam)) {
    		$PageParam = C("VAR_PAGE");
    	}
    	$Page = new \Page($Total_Size, $Page_Size, $Current_Page, $listRows, $PageParam, $PageLink, $Static);
    	$Page->SetPager('default', '{first}{prev}{liststart}{list}{listend}{next}{last}', array("listlong" => "9", "first" => "首页", "last" => "尾页", "prev" => "上一页", "next" => "下一页", "list" => "*", "disabledclass" => ""));
    	return $Page;
    }

    //空操作
    public function _empty() {
        $this->error('该页面不存在！');
    }
    
     /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的tip页面
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    public function tip($message, $jumpUrl='', $ajax=false) {
        if(true === $ajax || IS_AJAX) {// AJAX提交
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   0;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
        if(is_int($ajax)) $this->assign('waitSecond',$ajax);
        if(!empty($jumpUrl)) $this->assign('jumpUrl',$jumpUrl);
        // 提示标题
        $this->assign('msgTitle', '温馨提示');
        //如果设置了关闭窗口，则提示完毕后自动关闭窗口
        if($this->get('closeWin'))    $this->assign('jumpUrl','javascript:window.close();');
        //保证输出不受静态缓存影响
        C('HTML_CACHE_ON',false);
        $this->assign('message', $message);// 提示信息
        //发生错误时候默认停留3秒
        if(!isset($this->waitSecond)) {
            $this->assign('waitSecond','3');
        }
        // 默认发生错误的话自动返回上页
        if(!isset($this->jumpUrl)) {
            $this->assign('jumpUrl', "javascript:history.back(-1);");
        }
        $this->display(C('TMPL_ACTION_TIP'));
        exit(0);
    }

    /**
     * 检查操作频率
     * @param int $duration 距离最后一次操作的时长
     */
    protected function check_last_action($duration){
    	
    	$action = MODULE_NAME."-".CONTROLLER_NAME."-".ACTION_NAME;
    	$time = time();
    	$last_action = session('last_action');
    	if(!empty($last_action) && isset($last_action['action']) && $action == $last_action['action']){
    		$mduration = $time - $last_action['time'];
			
    		if($duration > $mduration){
    			$this->error("您的操作太过频繁，请稍后再试~~~");
    		} else {
				session('last_action.time', $time);
    		}
    	}else{
			session('last_action', array('action' => $action, 'time' => $time));
    	}
    }

}
