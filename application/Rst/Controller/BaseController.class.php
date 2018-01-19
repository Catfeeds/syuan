<?php
namespace Rst\Controller;
use Think\Controller\RestController;
use Common\Lib\Xxtea;

Class BaseController extends RestController {

    protected $allowMethod  = array('get', 'post'); // REST允许的请求类型列表
    protected $defaultMethod= 'get';//REST默认请求类型
    protected $allowType    = array('json', 'xml'); // REST允许请求的资源类型列表
    protected $defaultType  = 'json';//REST默认的资源类型
    protected $allowOutputType = array('json' => 'application/json', 'xml' => 'application/xml');//REST允许输出的资源类型列表

    //数据集合
    protected $app_id = 0;//应用ID
    protected $app_info = array();//应用信息
    protected $result = array('code' => 0, 'msg' => '', 'data' => array());//响应信息
    protected $client_info = array();//设备信息
    protected $user = array();//用户信息
    protected $api = array();//API信息

    public function __construct() {
        parent::__construct();
        //判断请求类型
        $this->_init_type();

        //当前请求APPID
        $this->app_id = I('_appid', 0, 'intval');
        if($this->app_id > 0) {
            $this->app_info = D('Common/App')->field('type,name,small_appid,small_appsecret,encryption,apilog,key,status')->find($this->app_id);
        }
        
        if(empty($this->app_info) || !$this->app_info['status']) {
            $this->result['code'] = 8404;
            $this->result['msg'] = 'app not exits';
            $this->response($this->result, $this->_type);
        }

        if(in_array($this->app_info['type'], array(1, 2, 3))) {//APP
            $this->_init_app();
        } else if($this->app_info['type'] == 4) {//Web
            $this->_init_webapp();
        } else if($this->app_info['type'] == 5) {//小程序
            $this->_init_smallapp();
        }
        $api_path = '/'.strtolower(MODULE_NAME).'/'.strtolower(CONTROLLER_NAME).'/'.strtolower(ACTION_NAME);
        $this->api = D('Common/Api')->where(array('path' => $api_path, 'version' => $this->client_info['api_version']))->find();
        
        if(!$this->api) {
            $this->_empty();
        }
        if($this->api['oauth']) {
            $this->_oauth();
        }
    }

     /**
     * App请求接口初始化
     */
    private function _init_app() {
        $_app_version = I('_app_version', 0, 'trim');//APP版本号
        $_api_version = I('_api_version', 0, 'intval');//接口版本号
        $_os_type = I('_os_type', 0, 'intval');//手机类型1:Android,2:IOS,3:WinPhone
        $_from = I('_from', '', 'trim');//渠道号
        $_os_version = I('_os_version', '', 'trim');//系统版本号
        $_dev_name = I('_dev_name', '', 'trim');//设备名称
        $_dev_model = I('_dev_model', '', 'trim');//设备型号
        $_dev_width = I('_dev_width', 0, 'intval');//设备屏幕宽度
        $_dev_height = I('_dev_height', 0, 'intval');//设备屏幕高度
        $_dev_dpi = I('_dev_dpi', 0, 'floatval');//设备屏幕DPI
        $_dev_token = I('_dev_token', '', 'trim');//设备ID
        $_network = I('_network', '', 'trim');//设备网络
        $_language = I('_language', '', 'trim');//设备语言
        
        if(!in_array($_os_type, array(1, 2, 3))) {
            $this->result['code'] = 8102;
            $this->result['msg'] = '系统类型错误';
            $this->response($this->result, $this->_type);
        } elseif(empty($_os_version)) {
            $this->result['code'] = 8103;
            $this->result['msg'] = '缺失系统版本类型参数';
            $this->response($this->result, $this->_type);
        } elseif(empty($_dev_model)) {
            $this->result['code'] = 8104;
            $this->result['msg'] = '缺失设备型号参数';
            $this->response($this->result, $this->_type);
        } elseif($_api_version <= 0) {
            $this->result['code'] = 8105;
            $this->result['msg'] = '缺失接口版本号参数';
            $this->response($this->result, $this->_type);
        } elseif(empty($_dev_token)) {
            $this->result['code'] = 8106;
            $this->result['msg'] = '缺失设备ID参数';
            $this->response($this->result, $this->_type);
        } else {
            $this->client_info = array(
                'app_id' => $this->app_id,
                'version_code' => $_app_version,
                'api_path' => '/'.strtolower(MODULE_NAME).'/'.strtolower(CONTROLLER_NAME).'/'.strtolower(ACTION_NAME),
                'api_version' => $_api_version,                
                'device_name' => $_dev_name,
                'device_token' => $_dev_token,
                'devwidth' => $_dev_width,
                'devheight' => $_dev_height,
                'devdpi' => $_dev_dpi,
                'systype' => $_os_type,
                'sysversion' => $_os_version,
                'model' => $_dev_model,
                'from' => $_from,
                'network' => $_network,
                'language' => $_language,
                'ip' => get_client_ip()
            );
        }

        if(empty($this->client_info)) {
            $this->result['code'] = 8400;
            $this->result['msg'] = '客户端基本参数为空';
            $this->response($this->result, $this->_type);
        }
        
        $this->_check_device();

        if(APP_DEBUG || $this->app_info['apilog']) {
            $this->_record_api_log();
        }        
    }

    /**
     * WebApp请求接口初始化
     */
    private function _init_webapp() {
        $this->client_info = array(
            'api_version' => I('_api_version', 1, 'intval'),
            'ip' => get_client_ip()
        );
    }

    /**
     * 小程序请求接口初始化
     */
    private function _init_smallapp() {
        $this->client_info = array(
            'api_version' => I('_api_version', 1, 'intval'),
            'ip' => get_client_ip()
        );
    }

    /**
     * 空方法
     */
    function _empty() {
        $this->response('', $this->_type, 404);
    }

    /**
     * 登录用户认证
     */
    protected function _oauth() {
        if(in_array($this->app_info['type'], array(1, 2, 3))) {//APP
            $_user_token = I('_user_token', '', 'trim');
            if($_user_token) {
                if($this->client_info['access_token'] == $_user_token) {
                    $leftseconds = $this->client_info['token_expire'] - TIMESTAMP;
                    if($leftseconds > 0) {
                        $user = M('Users')->find(intval($this->client_info['uid']));
                        if($user['user_status'] == 2) {
                            $this->result['code'] = 10001;
                            $this->result['msg'] = '账号未激活, 请先激活账号';
                            $this->response($this->result, $this->_type);
                        } else if($user['user_status'] == 0) {
                            $this->result['code'] = 10002;
                            $this->result['msg'] = '账号已被禁用, 请联系管理员';
                            $this->response($this->result, $this->_type);
                        } else {
                            $this->user = $user;
                            if($leftseconds < 3 * 86400) {
                                //自动延长授权
                            }
                            return true;
                        }                        
                    }
                }
            }
        } else if($this->app_info['type'] == 4) {//Web
            if(sp_is_user_login()) {
                $user = M('Users')->find(intval(sp_get_current_userid()));
                if($user) {
                    $session_user = session('user');
                    if($session_user['id'] == $user['id']) {
                        if($user['user_status'] == 2) {
                            $this->result['code'] = 10001;
                            $this->result['msg'] = '账号未激活, 请先激活账号';
                            $this->response($this->result, $this->_type);
                        } else if($user['user_status'] == 0) {
                            $this->result['code'] = 10002;
                            $this->result['msg'] = '账号已被禁用, 请联系管理员';
                            $this->response($this->result, $this->_type);
                        } else {
                            $this->user = $user;
                            return true;
                        }
                    }
                }
            }
        } else if($this->app_info['type'] == 5) {//小程序
            $_user_token = I('_user_token', '', 'trim');
            $session = M('AppSmallappSession')->where(array('app_id' => $this->app_id, 'token' => $_user_token))->find();
            if($session && $session['token_expire'] > TIMESTAMP) {
                $user = M('Users')->find($session['uid']);
                if($user['user_status'] == 2) {
                    $this->result['code'] = 10001;
                    $this->result['msg'] = '账号未激活, 请先激活账号';
                    $this->response($this->result, $this->_type);
                } else if($user['user_status'] == 0) {
                    $this->result['code'] = 10002;
                    $this->result['msg'] = '账号已被禁用, 请联系管理员';
                    $this->response($this->result, $this->_type);
                } else {
                    $this->user = $user;
                    return true;
                }
            }
        }
        $this->result['code'] = 8401;
        $this->result['msg'] = '认证失败, 请先登录';
        $this->response($this->result, $this->_type);
    }

    /*设备检测*/
    protected function _check_device() {
        $where = array(
                'app_id' => $this->client_info['app_id'],
                'device_token' => $this->client_info['device_token'],
                'systype' => $this->client_info['systype'],
                'model' => $this->client_info['model']
            );
        $device = M('AppDevice')->where($where)->find();
        if($device) {
            $update = array();
            foreach($device as $key => $val) {
                if(isset($this->client_info[$key]) && $val != $this->client_info[$key]) {
                    $update[$key] = $this->client_info[$key];
                }
            }
            $update['did'] = $device['did'];
            $update['update_ip'] = $this->client_info['ip'];
            $update['update_at'] = date('Y-m-d H:i:s', TIMESTAMP);
            M('AppDevice')->save($update);
            foreach($update as $k => $v) {
                $device[$k] = $v;
            }
        } else {
            $device = array(
                'app_id' => $this->client_info['app_id'],
                'version_code' => $this->client_info['version_code'],
                'uid' => 0,
                'access_token' => '',
                'token_expire' => '',             
                'device_name' => $this->client_info['device_name'],
                'device_token' => $this->client_info['device_token'],
                'devwidth' => $this->client_info['devwidth'],
                'devheight' => $this->client_info['devheight'],
                'devdpi' => $this->client_info['devdpi'],
                'systype' => $this->client_info['systype'],
                'sysversion' => $this->client_info['sysversion'],
                'model' => $this->client_info['model'],
                'from' => $this->client_info['from'],
                'language' => $this->client_info['language'],
                'create_ip' => $this->client_info['ip'],
                'create_at' => date('Y-m-d H:i:s', TIMESTAMP)
            );
            $did = M('AppDevice')->add($device);
            $device['did'] = $did;
        }
        //同步设备信息
        foreach($device as $key => $val) {
            $this->client_info[$key] = $val;
        }
    }

    /*记录接口请求日志*/
    protected function _record_api_log() {
        if(is_array($this->client_info)) {
            $data = $this->_mehtod == 'get' ? I('get.') : I('post.');
            $log = array(
                'api_path' => $this->client_info['api_path'],
                'api_version' => $this->client_info['api_version'],
                'data' => serialize($data),
                'uid' => empty($this->user) ? 0 : $this->user['id'],
                'did' => $this->client_info['did'],
                'app_id' => $this->app_id,
                'version_code' => $this->client_info['version_code'],
                'model' => $this->client_info['model'],
                'language' => $this->client_info['language'],
                'network' => $this->client_info['network'],
                'from' => $this->client_info['from'],
                'ip' => $this->client_info['ip'],
                'create_at' => date('Y-m-d H:i:s', TIMESTAMP)
            );
            M('AppApiLog')->add($log);
        }
    }

    /**
     * 请求类型初始化
     */
    private function _init_type() {
        if(__EXT__ && in_array(__EXT__, $this->allowType)) {
            $this->_type   =  __EXT__ ;
        } else {
            $this->_type   =  $this->defaultType;
        }
    }

    /**
     * 字符串加密
     * @param string $str 被加密的字符串
     * @param string $key 加密密钥
     * @return string
     */
    protected function _encrypt($str, $key) {
        return Xxtea::encrypt($str, $key);
    }

    /**
     * 字符串解密
     * @param string $str 要解密的字符串
     * @param string $key 解密密钥
     * @return string
     */
    protected function _decrypt($str, $key) {
        return Xxtea::decrypt($str, $key);
    }

    /**
     * 响应Json数据
     * @param string $httpcode http状态码
     */
    function responseJSON($httpcode = 200) {
        $this->response($this->result, 'json', $httpcode);
    }

    /**
     * 响应XML数据
     * @param string $httpcode http状态码
     */
    function responseXML($httpcode = 200) {
        $this->response($this->result, 'json', $httpcode);
    }
}