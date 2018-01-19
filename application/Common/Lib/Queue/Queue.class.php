<?php
namespace Common\Lib\Queue;

use Common\Lib\Queue\QueueInterface;
use Common\Lib\Queue\Providers\Common\Fifo as CommonFifo;
use Common\Lib\Queue\Providers\Common\Lifo as CommonLifo;
use Common\Lib\Queue\Providers\Memcached\Fifo as MemcachedFifo;
use Common\Lib\Queue\Providers\Memcached\Lifo as MemcachedLifo;

class Queue {
	
	/**
     * @var QueueInterface
     */
    protected $provider = null;

    /**
     * 初始化队列
  	 * @param string $store 存储类型
  	 * @param string $type 队列类型
  	 * @param array  $params 初始化参数
  	 * @return Queue
     */
	function initProvider($store, $type, $params = array()) {
		if($store == 'Memcached') {
			$elements = array();
			if(isset($params['elements']) && is_array($params['elements'])) {
				$elements = $params['elements'];
			}
			$expires = null;
			if(isset($params['expires']) && is_numeric($params['expires'])) {
				$expires = $params['expires'];
			}
			$queuename = 'queue';
			if(isset($params['queuename']) && strlen($params['queuename']) > 0) {
				$queuename = $params['queuename'];
			}
			$memcached = new \Memcached();
			$config = C('MEMCACHED_QUEUE');
			if(!$config || !is_array($config)) {
				return false;
			}
        	if(false === $memcached->addServer($config[0], $config[1])) {
        		return false;
        	}
        	if($type == 'Fifo') {
        		$provider = new MemcachedFifo($memcached, $queuename, $elements, $expires);
        	} else {
        		$provider = new MemcachedLifo($memcached, $queuename, $elements, $expires);
        	}
			
		} else if($store == 'Common') {
			$elements = array();
			if(isset($params['elements']) && is_array($params['elements'])) {
				$elements = $params['elements'];
			}
			if($type == 'Fifo') {
				$provider = new CommonFifo($elements);
			} else {
				$provider = new CommonLifo($elements);
			}
		}
		$this->provider = $provider;
		return true;
	}

	function clear() {
		if(!is_subclass_of($this->provider, 'Common\Lib\Queue\QueueInterface')) {
			return false;
		}
		return $this->provider->clear();
	}
	
	function count() {
		if(!is_subclass_of($this->provider, 'Common\Lib\Queue\QueueInterface')) {
			return false;
		}
		return $this->provider->count();
	}

	function pop() {
		if(!is_subclass_of($this->provider, 'Common\Lib\Queue\QueueInterface')) {
			return false;
		}
		return $this->provider->pop();
	}

	function push($item) {
		if(!is_subclass_of($this->provider, 'Common\Lib\Queue\QueueInterface')) {
			return false;
		}
		return $this->provider->push($item);
	}

	public function getIterator() {
		if(!is_subclass_of($this->provider, 'Common\Lib\Queue\QueueInterface')) {
			return false;
		}
		return $this->provider->getIterator();
	}
}
