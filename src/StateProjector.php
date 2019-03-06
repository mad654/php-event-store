<?php

namespace mad654\eventstore;


use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;

/**
 *
 * Calculates last state of all named payload properties which it
 * finds in all StateChanged events in the given eventstream
 *
 * Class FinalStateProjector
 * @package mad654\eventstore
 */
class StateProjector implements EventStreamConsumer
{
    /**
     * @var array
     */
    private $projection;

    /**
     * @var EventStream
     */
    private $stream;

    /**
     * @var \DateTimeImmutable
     */
    private $lastEventTimestamp;

    /**
     * @var string
     */
    private $lastEventType;

    /**
     * @var SubjectId
     */
    private $subjectId;

    /**
     * @var string
     */
    private $subjectType;

    /**
     * StateProjector constructor.
     */
    public function __construct()
    {
        $this->projection = [];
        $this->stream = new MemoryEventStream();
    }

    /**
     * returns iterator for all states. it returns the
     * state after each event which was in the stream
     * previously given to replay
     *
     * @param EventStream $stream
     * @return \Iterator|StateProjector[]
     */
    public static function intermediateIterator(EventStream $stream): \Iterator
    {
        $intermediate = new self();

        /* @var Event $event */
        foreach ($stream as $event) {
            $intermediate->on($event);

            if ($event instanceof ObjectCreatedEvent) {
                continue;
            }

            yield clone $intermediate;
        }
    }

    public function replay(EventStream $stream): void
    {
        $this->projection = [];
        $this->stream = $stream;

        foreach ($this->stream as $event) {
            if (!$event instanceof StateChanged) {
                continue;
            }

            $this->on($event);
        }
    }

    public function on(Event $event): void
    {
        if ($event instanceof ObjectCreatedEvent) {
            $this->subjectType = $event->get('class_name');
            $this->subjectId = $event->subjectId();
            return;
        }

        $this->lastEventTimestamp = $event->timestamp();

        try {
            $this->lastEventType = (new \ReflectionClass($event))->getShortName();
        } catch (\ReflectionException $reflectionException) {
            $this->lastEventType = null;
        }

        foreach ($event->payload() as $key => $value) {
            $this->projection[$key] = $value;
        }
    }

    public function lastEventTimestamp(): ?\DateTimeImmutable
    {
        return $this->lastEventTimestamp;
    }

    public function lastEventType(): ?string
    {
        return $this->lastEventType;
    }

    public function subjectId(): ?SubjectId
    {
        return $this->subjectId;
    }

    public function subjectType(): ?string
    {
        return $this->subjectType;
    }

    public function projection(): array
    {
        return $this->projection;
    }

    public function get(string $property, $default = null)
    {
        if (!array_key_exists($property, $this->projection)) {
            return $default;
        }

        return $this->projection[$property];
    }
}