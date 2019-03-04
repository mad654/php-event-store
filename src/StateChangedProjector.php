<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStream;

class StateChangedProjector implements EventStreamConsumer
{

    /**
     * StateChangedProject constructor.
     */
    public function __construct()
    {
    }

    public function replay(EventStream $stream): void
    {
        // TODO: Implement replay() method.
    }
}