<?php

namespace mad654\eventstore;


class TestSubject implements EventStreamEmitter
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var Event[]
     */
    private $events;

    public function __construct(string $id)
    {
        $this->id = $id;
        $this->events = [new TestEvent($id)];
    }

    /**
     * @return string
     */
    public function subjectId(): string
    {
        return $this->id;
    }


    public function events(): array
    {
        return $this->events;
    }
}