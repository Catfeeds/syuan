<?php
namespace Activity\Model;

use Common\Model\CommonModel;

/**
 * 早起挑战注册表
 */
class JoinModel extends CommonModel{
    protected $tableName = 'activity_join';

    //注册
    function doJoin($uid, $activity) {
        $data = array('uid' => $uid,  'activity' => $activity, 'create_at' => date('Y-m-d H:i:s', time()));
        return $this->add($data);
    }

    //是否注册
    function isJoin($uid, $activity) {
        $where = array('uid' => $uid,  'activity' => $activity);
        return $this->where($where)->count() > 0;
    }
}
