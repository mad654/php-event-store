<?php

namespace mad654\eventstore\example;


use mad654\eventstore\Event;
use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventSourcedObject;
use mad654\eventstore\EventStream\AutoTrackingEventSourcedObjectTrait;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;

class LightSwitch implements EventSourcedObject
{
    use AutoTrackingEventSourcedObjectTrait;

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
    private $state;

    public function __construct(string $id)
    {
        $this->events = new MemoryEventStream();
        $this->record(new StateChanged(['id' => $id, 'state' => false]));
        $this->constructorInvocationCount++;
    }

    public function subjectId(): string
    {
        return $this->id;
    }

    public function isOn(): bool
    {
        return $this->state;
    }

    public function switchOn()
    {
        if ($this->state) return;
        $this->record(new StateChanged(['state' => true]));
    }

    public function switchOff()
    {
        if (!$this->state) return;
        $this->record(new StateChanged(['state' => false]));
    }

    public function on(Event $event): void
    {
        $this->id = $event->get('id', $this->id);
        $this->state = $event->get('state', $this->state);
    }

}