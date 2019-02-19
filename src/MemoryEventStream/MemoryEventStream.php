<?php

namespace mad654\eventstore\MemoryEventStream;


use mad654\eventstore\Event;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\Fixtures\TestSubject;
use Traversable;

class MemoryEventStream implements EventStream
{
    /**
     * @var array
     */
    private $data;

    /**
     * MemoryEventStream constructor.
     */
    public function __construct()
    {
        $this->data = [];
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
        return new \ArrayIterator($this->data);
    }

    public function append(Event $event): EventStream
    {
        $this->data[] = $event;

        return $this;
    }

    public function appendUnknown(EventStream $other): void
    {
        # TODO: make sure we only import unknown events
        foreach ($other as $event) {
            $this->append($event);
        }
    }

    public function toEventStreamEmitter(): EventStreamEmitter
    {
        # TODO: refactor to common abstract base class?
        # TODO: do not call constructor
        # TODO: Load subject class from stream
        $subject = new TestSubject("fake");
        $subject->replay($this);
        return $subject;
    }
}