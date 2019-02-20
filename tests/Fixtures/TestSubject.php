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
        $stream->appendAll($this->events);
        # fixme: this makes subject unserializable
        $this->events = $stream;
    }

    public function replay(EventStream $stream): void
    {
        $this->id = null;
        # fixme: this makes subject unserializable
        $this->events = $stream;

        foreach ($stream->getIterator() as $event) {
            $this->on($event);
        }
    }

    private function on(Event $event)
    {
        # TODO support path syntax: path.to.payload.element
        # TODO document how to create new subject by example patient
        if (isset($event->payload()['someEventField'])) {
            $this->id = $event->payload()['someEventField'];
        }
    }

    public function dummyEventAction($i)
    {
        $event = new TestEvent($i);
        $this->on($event);
        $this->events->append($event);
    }
}