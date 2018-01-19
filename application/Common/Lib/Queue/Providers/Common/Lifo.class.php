<?php

namespace Common\Lib\Queue\Providers\Common;

use Common\Lib\Queue\QueueInterface;

/**
 * Last-In-First-Out queue (similar to a stack) implementation.
 */
class Lifo implements QueueInterface {

    /**
     * The queue as a raw list.
     *
     * @var mixed[]
     */
    private $elements = [];

    /**
     * Constructor.
     *
     * @param mixed[] $elements
     *            List with elements to add to queue.
     */
    public function __construct(array $elements = []) {
        $this->elements = $elements;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::clear()
     */
    public function clear() {
        $this->elements = [];

        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::count()
     */
    public function count() {
        return count($this->elements);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::pop()
     */
    public function pop() {
        return array_pop($this->elements);
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Common\Lib\Queue\QueueInterface::push($item)
     */
    public function push($item) {
        $this->elements[] = $item;

        return $this;
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see IteratorAggregate::getIterator()
     */
    public function getIterator() {

        return new \ArrayIterator($elements);
    }
}