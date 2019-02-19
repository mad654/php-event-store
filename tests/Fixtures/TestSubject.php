<?php

namespace mad654\eventstore\Fixtures;


use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;

class TestSubject implements EventStreamEmitter
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var EventStream
     */
    private $events;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->events = new MemoryEventStream();
        $this->events->append(new TestEvent($id));
    }

    public function subjectId(): string
    {
        return $this->id;
    }

    public function emitEventsTo(EventStream $stream): void
    {
        $stream->appendUnknown($this->events);
    }
}