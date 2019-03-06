<?php

namespace mad654\eventstore\EventStream;


use mad654\eventstore\Event;
use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;
use mad654\eventstore\SubjectId;

trait AutoTrackingEventSourcedObjectTrait
{
    /**
     * @var SubjectId
     */
    private $id;

    /**
     * @var EventStream
     */
    private $events;

    private function init(SubjectId $id, array $initialState)
    {
        $this->id = $id;
        $this->events = new MemoryEventStream();
        $this->record(new StateChanged($id, $initialState));
    }

    public function subjectId(): SubjectId
    {
        return $this->id;
    }

    public function replay(EventStream $stream): void
    {
        $this->id = null;
        $this->events = $stream;

        /* @var Event $event */
        foreach ($stream->getIterator() as $event) {
            if (is_null($this->id)) {
                $this->id = $event->subjectId();
            }

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