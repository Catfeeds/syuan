<?php
namespace Rst\Controller;
use Rst\Controller\BaseController;

/**
* 小程序API专用基类接口
*/
class BasesmallappController extends BaseController {
	
	public function __construct() {
        parent::__construct();
        if($this->app_info['type'] != 5) {
        	$this->result['code'] = 8500;
            $this->result['msg'] = 'app type error';
            $this->response($this->result, $this->_type);
        }
    }
}