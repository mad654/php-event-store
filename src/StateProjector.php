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
     * @return \Iterator
     */
    public static function intermediateIterator(EventStream $stream): \Iterator
    {
        $intermediate = new self();
        $meta = [
            'subject' => [
                'id' => 'UNKNOWN',
                'class' => 'UNKNOWN'
            ]
        ];

        /* @var Event $event */
        foreach ($stream as $event) {
            if ($event instanceof ObjectCreatedEvent) {
                $meta['subject']['type'] = $event->get('class_name');
                continue;
            }

            $intermediate->on($event);
            $state = $intermediate->toArray();
            $state['__meta'] = $meta;
            $state['__meta']['timestamp'] = $event->timestamp()->format(DATE_ATOM);
            $state['__meta']['subject']['id'] = $event->subjectId();

            try {
                $state['__meta']['type'] = (new \ReflectionClass($event))->getShortName();
            } catch (\ReflectionException $reflectionException) {
                $state['__meta']['type'] = 'UNKNOWN';
            }

            yield $state;
        }
    }

    public function toArray(): array
    {
        return $this->projection;
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
        foreach ($event->payload() as $key => $value) {
            $this->projection[$key] = $value;
        }
    }
}