<?php

namespace Common\Lib\Queue;

use IteratorAggregate;
/**
 * Interface for queue implementations.
 */
interface QueueInterface extends IteratorAggregate {

    /**
     * Removes all elements from the queue.
     *
     * @return QueueInterface Reference to this instance.
     */
    public function clear();
    /**
     * Returns the count of elements stored in the queue.
     *
     * @return int The count of elements stored in the queue.
     */
    public function count();
    /**
     * Dequeues an element from the queue.
     *
     * @return mixed Dequeued element.
     */
    public function pop();
    /**
     * Enqueues an element into the queue.
     *
     * @param mixed $item
     *            Element to enqueue.
     * @return QueueInterface Reference to this instance.
     */
    public function push($item);
}