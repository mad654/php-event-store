<?php

namespace mad654\eventstore\example;


use mad654\eventstore\Event;
use mad654\eventstore\event\StateChanged;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;

/**
 * Class TestSubject
 * @package mad654\eventstore\Fixtures
 *
 * TODO refactor to lighter example
 */
class LightSwitch implements EventStreamEmitter
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
     * @var string
     */
    private $kitchen;

    /**
     * @var EventStream
     */
    public $events;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->events = new MemoryEventStream();
        $this->events->append(new StateChanged(['id' => $id, 'kitchen' => false]));
        $this->constructorInvocationCount++;
    }

    public function subjectId(): string
    {
        return $this->id;
    }

    public function isKitchenOn(): bool
    {
        return $this->kitchen;
    }

    public function switchKitchenOn()
    {
        if ($this->kitchen) return;
        $event = new StateChanged(['kitchen' => true]);
        $this->record($event);
    }

    public function switchKitchenOff()
    {
        if (!$this->kitchen) return;
        $event = new StateChanged(['kitchen' => false]);
        $this->record($event);
    }

    private function on(Event $event)
    {
        $this->id = $event->get('id', $this->id);
        $this->kitchen = $event->get('kitchen', $this->kitchen);
    }

    public function emitEventsTo(EventStream $stream): void
    {
        $stream->appendAll($this->events);
        # fixme: this makes subject unserializable, maybe we can feed in a proxy stream, which uses static calls to retrieve the real stream
        $this->events = $stream;
    }

    public function replay(EventStream $stream): void
    {
        $this->id = null;
        # fixme: this makes subject unserializable, maybe we can feed in a proxy stream, which uses static calls to retrieve the real stream
        $this->events = $stream;

        foreach ($stream->getIterator() as $event) {
            $this->on($event);
        }
    }

    /**
     * @param StateChanged $event
     */
    private function record(StateChanged $event): void
    {
        $this->on($event);
        $this->events->append($event);
    }
}