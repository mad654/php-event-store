<?php

namespace mad654\eventstore\EventStream;


use mad654\eventstore\Event\StateChanged;

trait AutoTrackingEventSourcedObjectTrait
{

    /**
     * @var EventStream
     */
    private $events;

    public function replay(EventStream $stream): void
    {
        $this->id = null;
        $this->events = $stream;

        foreach ($stream->getIterator() as $event) {
            $this->on($event);
        }
    }

    public function emitEventsTo(EventStream $stream): void
    {
        $stream->appendAll($this->events);
        $this->events = $stream;
    }

    /**
     * @param StateChanged $event
     */
    protected function record(StateChanged $event): void
    {
        $this->on($event);
        $this->events->append($event);
    }

    public function history(EventStreamRenderer $renderer): EventStreamRenderer
    {
        $renderer->render($this->events);
        return $renderer;
    }
}