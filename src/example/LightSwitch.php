<?php

namespace mad654\eventstore\example;


use mad654\eventstore\Event;
use mad654\eventstore\event\StateChanged;
use mad654\eventstore\EventStream\AutoTrackingEventStreamEmitterTrait;
use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;

class LightSwitch implements EventStreamEmitter
{
    use AutoTrackingEventStreamEmitterTrait;

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

    public function __construct(string $id)
    {
        $this->events = new MemoryEventStream();
        $this->record(new StateChanged(['id' => $id, 'kitchen' => false]));
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
        $this->record(new StateChanged(['kitchen' => true]));
    }

    public function switchKitchenOff()
    {
        if (!$this->kitchen) return;
        $this->record(new StateChanged(['kitchen' => false]));
    }

    private function on(Event $event)
    {
        $this->id = $event->get('id', $this->id);
        $this->kitchen = $event->get('kitchen', $this->kitchen);
    }

}