<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStreamEmitter;

class ObjectCreatedEvent implements Event
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
        $this->className = $className;
    }


    public static function for(EventStreamEmitter $emitter): ObjectCreatedEvent
    {
        return new ObjectCreatedEvent(get_class($emitter));
    }

    public function payload(): array
    {
        return [
            'class_name' => $this->className
        ];
    }
}