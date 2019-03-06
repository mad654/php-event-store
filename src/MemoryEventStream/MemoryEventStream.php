<?php

namespace mad654\eventstore\MemoryEventStream;


use mad654\eventstore\Event;
use mad654\eventstore\EventStream\EventStream;
use Traversable;

final class MemoryEventStream implements EventStream
{
    /**
     * @var array
     */
    private $data;

    /**
     * MemoryEventStream constructor.
     */
    public function __construct()
    {
        $this->data = [];
    }

    public static function fromArray(array $events): self
    {
        $instance = new self();

        foreach ($events as $idx => $event) {
            if (!$event instanceof Event) {
                throw new \RuntimeException(sprintf(
                    'Element at index %s does not implement Event',
                    $idx
                ));
            }
            $instance->data[] = $event;
        }

        return $instance;
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->data);
    }

    public function append(Event $event): EventStream
    {
        $this->data[] = $event;

        return $this;
    }

    public function appendAll(EventStream $other): void
    {
        foreach ($other as $event) {
            $this->append($event);
        }
    }
}