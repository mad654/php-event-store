<?php

namespace mad654\eventstore;


/**
 *
 * Persists objects by storing
 * there events
 *
 */
class EventObjectStore
{
    /**
     * @var EventStreamFactory
     */
    private $streamFactory;

    /**
     * EventObjectStore constructor.
     * @param EventStreamFactory $streamFactory
     */
    public function __construct(EventStreamFactory $streamFactory)
    {
        $this->streamFactory = $streamFactory;
    }


    public function attach(EventStreamEmitter $emitter): void
    {
        $stream = $this->streamFactory->new($emitter->subjectId());

        foreach ($emitter->events() as $event) {
            $stream->attach($event);
        }
    }
}