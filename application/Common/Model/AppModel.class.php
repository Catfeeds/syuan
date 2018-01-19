<?php
namespace Common\Model;
use Common\Model\CommonModel;
class AppModel extends CommonModel{

    protected $tableName = 'app';
    protected $appTypes = array(1 => 'Android', 2 => 'IOS', 3 => 'WinPhone', 4 => 'WebApp', 5 => '微信小程序');

    function getTypes() {
        return $this->appTypes;
    }
}
