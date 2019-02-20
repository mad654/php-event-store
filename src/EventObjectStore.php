<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\EventStream\EventStreamFactory;
use mad654\eventstore\Fixtures\TestSubject;

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
            return $this->toEventStreamEmitter($stream);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(
                "Object with id `$key` not found",
                404,
                $e
            );
        }
    }

    private function toEventStreamEmitter($stream): EventStreamEmitter
    {
        # TODO: refactor to common abstract base class?
        # TODO: Load subject class from stream
        $subjectType = TestSubject::class;

        try {
            $class = new \ReflectionClass($subjectType);
            $subject = $class->newInstanceWithoutConstructor();
            $subject->replay($stream);

            if ($subject instanceof EventStreamEmitter) {
                return $subject;
            }
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(
                "Could not recreate object of type: " . $subjectType,
                500,
                $e
            );
        }

        # TODO: throw exception to make this error recoverable?
    }
}