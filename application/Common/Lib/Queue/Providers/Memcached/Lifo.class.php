<?php

namespace Common\Lib\Queue\Providers\Memcached;

use \Memcached;
use Common\Lib\Queue\QueueInterface;

/**
 * Last-In-First-Out queue (similar to a stack) implementation using memcached.
 */
class Lifo implements QueueInterface {

    /**
     * The used memcached instance.
     *
     * @var Memcached
     */
    private $memcached = null;

    /**
     * Name of the queue.
     *
     * @var string
     */
    private $queueName = null;

    /**
     * tail of the queue name.
     *
     * @var string
     */
    private $queueTail = '--max-enqueued';

    /**
     * Expiration time of the element in seconds.
     *
     * @var int
     */
    private $expire = 7 * 86400;

    /**
     * Constructor.
     *
     * @param Memcached $memcached
     *            The used memcached instance.
     * @param string $queueName
     *            Name of the queue.
     * @param mixed[] $elements
     *            List with elements to add to queue.
     * @param int $expire
     *            Expiration time of the element in seconds.
     */
    public function __construct(Memcached $memcached, string $queueName, array $elements = [], int $expire = null) {
        $this->memcached = $memcached;
        $this->queueName = $queueName;

        if($expire) {
            $this->expire = $expire;
        }

        foreach ($elements as $element) {
            $this->push($element);
        }
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::clear()
     */
    public function clear() {
        $maxEnqueued = (int) $this->memcached->get($this->queueName.$this->queueTail);

        if($maxEnqueued) {
            $elementNames = array_map(function ($id) {
                return $this->queueName.'--'.$id;
            }, range(2, $maxEnqueued));

            $this->memcached->deleteMulti($elementNames);
            $this->memcached->delete($this->queueName.$this->queueTail);
        }
        
        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::count()
     */
    public function count() {
        $maxEnqueued = (int) $this->memcached->get($this->queueName.$this->queueTail);

        if($maxEnqueued <= 1) {
            return 0;
        }

        return $maxEnqueued - 1;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::dequeue()
     */
    public function pop() {
        $id = $this->memcached->decrement($this->queueName.$this->queueTail);

        if($id === false) {
            return null;
        }

        if ($id > 0) {
            // because the queue starts with 1 and not with 0,
            // we have to increment the id to get the real id.
            ++ $id;

            $result = $this->memcached->get($this->queueName.'--'.$id);

            $this->memcached->delete($this->queueName.'--'.$id);

            return $result;
        }

        $this->memcached->increment($this->queueName.$this->queueTail);

        return null;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::push($item)
     */
    public function push($item) {
        $id = $this->memcached->increment($this->queueName.$this->queueTail);

        if($id === false) {
            if(!$this->memcached->add($this->queueName.$this->queueTail, 2, $this->expire)) {
                $id = $this->memcached->increment($this->queueName.$this->queueTail);
            } else {
                $id = 2;
            }
        }

        $this->memcached->set($this->queueName.'--' .$id, $item, $this->expire);

        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator() {
        $maxEnqueued = (int) $this->memcached->get($this->queueName.$this->queueTail);

        $elements = [];

        if($maxEnqueued) {
            $elementNames = array_map(function ($id) {
                return $this->queueName.'--'.$id;
            }, range(2, $maxEnqueued));

            $cas = null;
            $elements = array_values($this->memcached->getMulti($elementNames));
        }

        return new \ArrayIterator($elements);
    }
}