<?php

namespace mad654\eventstore;


use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventStream\EventStream;

/**
 *
 * Calculates last state of all named payload properties which it
 * finds in all StateChanged events in the given eventstream
 *
 * Class FinalStateProjector
 * @package mad654\eventstore
 */
class FinalStateProjector implements EventStreamConsumer
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