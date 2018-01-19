<?php
namespace Common\Controller;
use Think\Controller;

class ClibaseController extends Controller {

	protected $cache_tail = '_tail';
    function _initialize() {
        $sapi_type = php_sapi_name();
        if(strtolower($sapi_type) != 'cli') {
            throw new \Exception('cli mode supported only!');
        }
		$this->cache_tail = md5(C('DB_HOST').C('DB_NAME').C('DB_PREFIX'));
    }
}
