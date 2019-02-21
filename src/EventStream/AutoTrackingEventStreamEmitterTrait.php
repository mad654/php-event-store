<?php

namespace mad654\eventstore\EventStream;


use mad654\eventstore\event\StateChanged;

trait AutoTrackingEventStreamEmitterTrait
{

    /**
     * @var EventStream
     */
    public $events;

    public function replay(EventStream $stream): void
    {
        $this->id = null;
        # fixme: this makes subject unserializable, maybe we can feed in a proxy stream, which uses static calls to retrieve the real stream
        $this->events = $stream;

        foreach ($stream->getIterator() as $event) {
            $this->on($event);
        }
    }

    public function emitEventsTo(EventStream $stream): void
    {
        $stream->appendAll($this->events);
        # fixme: this makes subject unserializable, maybe we can feed in a proxy stream, which uses static calls to retrieve the real stream
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
}