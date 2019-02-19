<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\EventStream\EventStreamFactory;

/**
 *
 * Persists objects by storing
 * their events to given stream factory
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
        $emitter->emitEventsTo($stream);
    }

    public function get(string $key): EventStreamEmitter
    {
        try {
            $stream = $this->streamFactory->get($key);
            return $stream->toEventStreamEmitter();
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(
                "Object with id `$key` not found",
                404,
                $e
            );
        }
    }
}