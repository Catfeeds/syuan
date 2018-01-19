<?php
/**
 * 基于Memcached的并发锁
 * author lezhizhe_net@163.com
 * example:
 * $lock = new mcacheLock($host, $port);
 * if($lock->Lock('key')) {
 *     //TODO:
 *     $lock->unLock();
 * }
 */
namespace Common\Lib;


class mcacheLock {

    private $client = null; //Client客户端,Memcached
    private $islock = false;//是否成功锁定
    private $locktime = 300;//锁定时间5分钟
    private $mutex = 'mutex';//加锁键值

    /**
     * 构造函数
     * @param string $host 服务器
     * @param integer $port 端口
     */
    public function __construct($host, $port) {
        $this->init($host, $port);
    }

    private function init($host, $port) {
        $this->client = new Memcached();
        $this->client->addServer($host, $port);
    }

    //加锁
    public function Lock($mutex) {
        $this->mutex = $mutex;
        $cas_token = 0.0;
        $rt = $this->client->get($this->mutex, null, $cas_token);
        while(false === $rt || $this->client->getResultCode() === Memcached::RES_NOTFOUND) {
            $rt = $this->client->add($this->mutex, 1, $this->locktime);
            if(false === $rt || $this->client->getResultCode() === Memcached::RES_NOTSTORED) {
                $rt = $this->client->get($this->mutex, null, $cas_token);
            } else {
                //Key设置成功,加锁成功
                $this->islock = true;
                return true;
            }
        }
        return false;
    }

    //判断是否成功锁定
    public function isLock() {
        return $this->islock;
    }

    //解锁
    public function unLock() {
        $this->client->delete($this->mutex);
    }
}
