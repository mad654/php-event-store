<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\EventStream\EventStreamFactory;

/**
 *
 * Persists objects by storing
 * their events
 *
 */
class EventObjectStore
{
    /**
     * @var EventStreamFactory
     */
    private $streamFactory;

    /**
     * @var EventStreamEmitter[]
     */
    private $objects;

    /**
     * EventObjectStore constructor.
     * @param EventStreamFactory $streamFactory
     */
    public function __construct(EventStreamFactory $streamFactory)
    {
        $this->objects = [];
        $this->streamFactory = $streamFactory;
    }


    public function attach(EventStreamEmitter $emitter): void
    {
        $stream = $this->streamFactory->new($emitter->subjectId());
        $emitter->emitEventsTo($stream);
        $this->objects[$emitter->subjectId()] = $emitter;
    }

    public function get(string $key): EventStreamEmitter
    {
        $stream = $this->streamFactory->get($key);
        # TODO: stream should not be returned here if it not exists
        $this->objects[$key] = $stream->toEventStreamEmitter();

        if (!array_key_exists($key, $this->objects)) {
            throw new \RuntimeException(
                "Object with id `$key` not found"
            );
        }

        return $this->objects[$key];
    }
}