<?php

namespace mad654\eventstore\event;


use mad654\eventstore\Event;
use PHPUnit\Framework\TestCase;

class StateChangedTest extends TestCase
{
    /**
     * @test
     */
    public function __construct_always_returnsInstanceOfEvent()
    {
        $this->assertInstanceOf(Event::class, new StateChanged([]));
    }
}
