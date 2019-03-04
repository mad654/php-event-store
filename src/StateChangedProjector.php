<?php

namespace mad654\eventstore;


use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventStream\EventStream;

class StateChangedProjector implements EventStreamConsumer
{
    private $projection;

    public function replay(EventStream $stream): void
    {
        $this->projection = [];

        foreach ($stream as $event) {
            if (!$event instanceof StateChanged) {
                continue;
            }

            foreach ($event->payload() as $key => $value) {
                $this->projection[$key] = $value;
            }
        }
    }

    public function toArray(): array
    {
        return $this->projection;
    }
}