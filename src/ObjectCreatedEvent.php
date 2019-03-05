<?php

namespace mad654\eventstore;


use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventStream\EventStreamEmitter;

class ObjectCreatedEvent extends StateChanged implements Event
{
    /**
     * @var string
     */
    private $className;

    /**
     * ObjectCreatedEvent constructor.
     * @param string $id
     * @param string $className
     */
    private function __construct(string $id, string $className)
    {
        parent::__construct($id, ['class_name' => $className]);
        $this->className = $className;

    }


    public static function for(EventStreamEmitter $emitter): ObjectCreatedEvent
    {
        return new ObjectCreatedEvent($emitter->subjectId(), get_class($emitter));
    }
}