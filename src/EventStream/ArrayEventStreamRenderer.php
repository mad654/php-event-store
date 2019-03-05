<?php

namespace mad654\eventstore\EventStream;


use mad654\eventstore\StateProjector;

class ArrayEventStreamRenderer implements EventStreamRenderer
{
    /**
     * @var array
     */
    private $history = [];

    public function render(EventStream $events): void
    {
        // TODO: Print Line per Property instead of per Event to keep max 80 width
        // TODO: Add table separator between events
        $this->history = [];

        /* @var \mad654\eventstore\StateProjector $state */
        foreach (StateProjector::intermediateIterator($events) as $state) {
            $entry = [
                count($this->history),
                $state->lastEventTimestamp()->format(DATE_ATOM),
                $state->lastEventType(),
                $state->subjectId(),
            ];

            foreach ($state->projection() as $key => $value) {
                $entry[] = $key;
                $entry[] = $value;
            }

            $this->history[] = $entry;
        }
    }

    public function toArray()
    {
        return $this->history;
    }
}