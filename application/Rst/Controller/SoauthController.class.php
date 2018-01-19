<?php
namespace Rst\Controller;
use Rst\Controller\BasesmallappController;
use GuzzleHttp\Client;
/**
* 小程序授权登录接口
*/
class SoauthController extends BasesmallappController {
	
	public function __construct() {
        parent::__construct();
    }

    /*微信授权登录*/
    public function login() {
    	if($this->user) {
    		$this->result['code'] = 8501;
            $this->result['msg'] = '您已是登录状态, 请勿重复登录!';
            $this->response($this->result, $this->_type);
    	}
    	$code = I('code', '', 'trim');
    	if(empty($code)) {
    		$this->result['code'] = 8502;
            $this->result['msg'] = '登录code参数缺失!';
            $this->response($this->result, $this->_type);
    	}
    	$client = new Client(array(
    		'base_uri' => 'https://api.weixin.qq.com',
    		'timeout'  => 3.0,
    	));
    	$response = $client->request('GET', '/sns/jscode2session?appid='.$this->app_info['small_appid'].'&secret='.$this->app_info['small_appsecret'].'&js_code=JSCODE&grant_type=authorization_code');
    	$code = $response->getStatusCode();
    	if($code == '200') {
    		$data = json_decode($response->getBody(), true);
    		if($data) {
    			if(isset($data['errcode'])) {
    				$this->result['code'] = $data['errcode'];
            		$this->result['msg'] = $data['errmsg'];
            		$this->response($this->result, $this->_type);
    			} else {
    				$openid = $data['openid'];
    				$session_key = $data['session_key'];
    			}
    		} else {
    			$this->result['code'] = 8503;
            	$this->result['msg'] = 'jscode2session get error!';
            	$this->response($this->result, $this->_type);
    		}
    	} else {
    		$this->result['code'] = 8503;
            $this->result['msg'] = 'https get jscode2session error!';
            $this->response($this->result, $this->_type);
    	}    	
    }

    /*更新延长token*/
    public function refresh_token() {

    }
}