<?php
namespace Common\Lib\Queue\Providers\Memcached;

use \Memcached;
use Common\Lib\Queue\QueueInterface;

/**
 * First-In-First-Out queue implementation using memcached.
 */
class Fifo implements QueueInterface {

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
     * Expiration time of the element in seconds.
     *
     * @var int
     */
    private $expire = null;

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
    public function __construct(Memcached $memcached, string $queueName, array $elements = [], int $expire = null)
    {
        $this->memcached = $memcached;
        $this->queueName = $queueName;
        if($expire) {
            $this->expire = $expire;
        }

        foreach($elements as $element) {
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
        $maxDequeued = (int) $this->memcached->get($this->queueName . '--max-dequeued');
        $maxEnqueued = (int) $this->memcached->get($this->queueName . '--max-enqueued');

        if ($maxDequeued !== $maxEnqueued) {
            $elementNames = array_map(function ($id) {
                return $this->queueName . '--' . $id;
            }, range($maxDequeued + 1, $maxEnqueued));

            $this->memcached->deleteMulti($elementNames);
            $this->memcached->delete($this->queueName . '--max-dequeued');
            $this->memcached->delete($this->queueName . '--max-enqueued');
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
        $maxDequeued = (int) $this->memcached->get($this->queueName . '--max-dequeued');
        $maxEnqueued = (int) $this->memcached->get($this->queueName . '--max-enqueued');

        return $maxEnqueued - $maxDequeued;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::pop()
     */
    public function pop() {
        $maxEnqueued = $this->memcached->get($this->queueName . '--max-enqueued');

        $id = $this->memcached->increment($this->queueName . '--max-dequeued');

        if ($id === false) {
            return null;
        }

        if ($id <= $maxEnqueued) {
            $result = $this->memcached->get($this->queueName . '--' . $id);

            $this->memcached->delete($this->queueName . '--' . $id);

            return $result;
        }

        $this->memcached->decrement($this->queueName . '--max-dequeued');

        return null;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::enqueue($item)
     */
    public function push($item) {
        $id = $this->memcached->increment($this->queueName . '--max-enqueued');

        if ($id === false) {
            if (! $this->memcached->add($this->queueName . '--max-enqueued', 1, $this->expire)) {
                $id = $this->memcached->increment($this->queueName . '--max-enqueued');
            } else {
                $id = 1;
                $this->memcached->add($this->queueName . '--max-dequeued', 0, $this->expire);
            }
        }

        $this->memcached->set($this->queueName . '--' . $id, $item, $this->expire);

        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator() {
        $maxDequeued = (int) $this->memcached->get($this->queueName . '--max-dequeued');
        $maxEnqueued = (int) $this->memcached->get($this->queueName . '--max-enqueued');

        $elements = [];

        if ($maxDequeued !== $maxEnqueued) {
            $elementNames = array_map(function ($id) {
                return $this->queueName . '--' . $id;
            }, range($maxDequeued + 1, $maxEnqueued));

            $cas = null;
            $elements = array_values($this->memcached->getMulti($elementNames));
        }

        return new \ArrayIterator($elements);
    }
}