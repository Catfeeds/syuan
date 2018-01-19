<?php
namespace Rst\Controller;
use Rst\Controller\BaseController;

/**
*  
*/
class EmptyController extends BaseController {
	
	function __construct() {
		parent::__construct();
	}

	function _empty() {
		if($this->api) {			
			if($this->api['analog_data']) {
				$this->api['analog_data'] = htmlspecialchars_decode($this->api['analog_data']);
				$this->setContentType($this->_type);
				die($this->api['analog_data']);
			} else {
				$this->result['code'] = 8500;
	            $this->result['msg'] = '接口无数据返回';
	            $this->response($this->result, $this->_type);
			}
		} else {
			parent::_empty();	
		}
	}
}