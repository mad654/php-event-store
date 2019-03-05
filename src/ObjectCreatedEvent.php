<?php

namespace mad654\eventstore;


use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventStream\EventStreamEmitter;

/**
 * Class ObjectCreatedEvent
 * @package mad654\eventstore
 *
 * FIXME Test if we loose timestamp after deserialisiatio
 */
class ObjectCreatedEvent extends StateChanged implements Event
{
    /**
     * ObjectCreatedEvent constructor.
     * @param string $id
     * @param string $className
     */
    private function __construct(string $id, string $className)
    {
        parent::__construct(StringSubjectId::fromString($id), ['class_name' => $className]);
    }


    public static function for(EventStreamEmitter $emitter): ObjectCreatedEvent
    {
        return new ObjectCreatedEvent($emitter->subjectId(), get_class($emitter));
    }
}