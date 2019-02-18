<?php

namespace mad654\eventstore;


class TestSubject implements EventStreamEmitter
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var EventStream
     */
    private $events;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->events = new MemoryEventStream();
        $this->events->append(new TestEvent($id));
    }

    /**
     * @return string
     */
    public function subjectId(): string
    {
        return $this->id;
    }


    public function events(): EventStream
    {
        return $this->events;
    }
}