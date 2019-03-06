<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\EventStream\EventStreamFactory;

/**
 *
 * Persists objects by storing
 * their events to given stream factory
 *
 */
class EventSourcedObjectStore
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
        $stream->append(ObjectCreatedEvent::for($emitter));
        $emitter->emitEventsTo($stream);
    }

    public function get(SubjectId $key): EventSourcedObject
    {
        try {
            $stream = $this->streamFactory->get($key);
            return $this->toEventSourcedObject($stream);
        } catch (\RuntimeException $e) {
            throw new \RuntimeException(
                "Object with id `$key` not found",
                404,
                $e
            );
        }
    }

    private function toEventSourcedObject(EventStream $stream): EventSourcedObject
    {
        $class = $this->extractSubjectClassName($stream);

        try {
            return $this->recoverFrom($stream, $class);
        } catch (\ReflectionException $e) {
            throw new \RuntimeException(
                "Could not recreate object of type: " . $class,
                500,
                $e
            );
        }
    }

    private function extractSubjectClassName(EventStream $stream): string
    {
        foreach ($stream->getIterator() as $event) {
            if (!$event instanceof ObjectCreatedEvent) {
                throw new \RuntimeException(
                    "Invalid stream: expected ObjectCreatedEvent as first event"
                );
            }

            return $event->payload()['class_name'];
        }
    }

    /**
     * @param EventStream $stream
     * @param string $subjectType
     * @return EventSourcedObject
     * @throws \ReflectionException
     */
    private function recoverFrom(EventStream $stream, string $subjectType): EventSourcedObject
    {
        $class = new \ReflectionClass($subjectType);
        $subject = $class->newInstanceWithoutConstructor();

        if (!$subject instanceof EventSourcedObject) {
            throw new \RuntimeException(sprintf(
                'Invalid subject type in stream - it does not implement: %s',
                EventSourcedObject::class
            ));
        }

        $subject->replay($stream);
        return $subject;
    }
}