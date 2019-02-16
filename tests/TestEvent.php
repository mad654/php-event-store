<?php

namespace mad654\eventstore;


class TestEvent implements Event
{
    /**
     * @var array
     */
    private $payload;

    /**
     * TestEvent constructor.
     * @param string $someEventField
     */
    public function __construct(string $someEventField)
    {
        $this->payload = ['someEventField' => $someEventField];
    }
}