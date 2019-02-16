<?php

namespace mad654\eventstore;


use Traversable;

/**
 * Class FileEventStream
 *
 * Can store/load/traverse over events stored in filesystem
 *
 * @package mad654\eventstore
 */
class FileEventStream implements EventStorable, EventTraversable
{
    /**+
     * @var array
     */
    private $data = [];

    /**
     * SubjectEventStore constructor.
     */
    public function __construct()
    {
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        yield false;
    }

    public function load()
    {
        return $this;
    }

    public function append($event)
    {
        $this->data[] = $event;
    }
}