<?php

namespace mad654\eventstore\Fixtures;


use mad654\eventstore\Event;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;

class TestSubject implements EventStreamEmitter
{
    /**
     * @var int
     */
    public $constructorInvocationCount = 0;

    /**
     * @var string
     */
    private $id;

    /**
     * @var EventStream
     */
    public $events;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->events = new MemoryEventStream();
        $this->events->append(new TestEvent($id));
        $this->constructorInvocationCount++;
    }

    public function subjectId(): string
    {
        return $this->id;
    }

    public function emitEventsTo(EventStream $stream): void
    {
        $stream->appendUnknown($this->events);
    }

    public function replay(EventStream $stream): void
    {
        $this->id = null;
        $this->events = new MemoryEventStream();

        foreach ($stream->getIterator() as $event) {
            $this->on($event);
        }
    }

    private function on(Event $event)
    {
        # TODO support path syntax: path.to.payload.element
        $this->id = $event->payload()['someEventField'];
        $this->events->append($event);
    }

    public function dummyEventAction($i)
    {
        $this->events->append(new TestEvent($i));
    }
}