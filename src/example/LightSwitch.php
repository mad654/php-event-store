<?php

namespace mad654\eventstore\example;


use mad654\eventstore\Event;
use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventSourcedObject;
use mad654\eventstore\EventStream\AutoTrackingEventSourcedObjectTrait;
use mad654\eventstore\SubjectId;

class LightSwitch implements EventSourcedObject
{
    use AutoTrackingEventSourcedObjectTrait;

    /**
     * @var int
     * @FIXME remove from here
     */
    public $constructorInvocationCount = 0;

    /**
     * @var bool
     */
    private $state;

    public function __construct(SubjectId $id)
    {
        $this->init($id, ['state' => false]);
        $this->constructorInvocationCount++;
    }

    public function isOn(): bool
    {
        return $this->state;
    }

    public function switchOn()
    {
        if ($this->state) return;
        $this->record(new StateChanged($this->subjectId(), ['state' => true]));
    }

    public function switchOff()
    {
        if (!$this->state) return;
        $this->record(new StateChanged($this->subjectId(), ['state' => false]));
    }

    public function on(Event $event): void
    {
        $this->state = $event->get('state', $this->state);
    }

}