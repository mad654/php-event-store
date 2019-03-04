<?php

namespace mad654\eventstore;


use PHPUnit\Framework\TestCase;

class StateChangedProjectorTest extends TestCase
{
    /**
     * @test
     */
    public function __construct_always_returnsEventConsumer()
    {
        $this->assertInstanceOf(EventStreamConsumer::class, new StateChangedProjector());
    }
}