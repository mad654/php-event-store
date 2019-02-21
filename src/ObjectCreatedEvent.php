<?php

namespace mad654\eventstore;


use mad654\eventstore\event\StateChanged;
use mad654\eventstore\EventStream\EventStreamEmitter;

class ObjectCreatedEvent extends StateChanged implements Event
{
    /**
     * @var string
     */
    private $className;

    /**
     * ObjectCreatedEvent constructor.
     * @param string $className
     */
    private function __construct(string $className)
    {
        parent::__construct(['class_name' => $className]);
        $this->className = $className;
    }


    public static function for(EventStreamEmitter $emitter): ObjectCreatedEvent
    {
        return new ObjectCreatedEvent(get_class($emitter));
    }
}