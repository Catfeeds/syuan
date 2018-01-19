<?php
namespace Rst\Controller;
use Rst\Controller\BaseController;

/**
*  
*/
class DemoController extends BaseController {
	
	function __construct() {
		parent::__construct();
	}

	function index() {
		$this->_oauth();
		print_r($this->user);
	}
}