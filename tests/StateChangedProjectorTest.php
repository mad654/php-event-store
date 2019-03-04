<?php

namespace mad654\eventstore;


use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;
use PHPUnit\Framework\TestCase;

class StateChangedProjectorTest extends TestCase
{
    /**
     * @test
     */
    public function __construct_always_returnsEventConsumer()
    {
        $this->assertInstanceOf(EventStreamConsumer::class, $this->instance());
    }

    /**
     * @test
     */
    public function toArray_always_returnsArray()
    {
        $this->assertSame([], $this->instance()->toArray());
    }

    /**
     * @test
     */
    public function toArray_eventWithOneProperty_returnsArrayWithOneProperty()
    {
        $events = [
            new StateChanged(['foo' => 'bar'])
        ];

        $this->assertSame(['foo' => 'bar'], $this->instance($events)->toArray());
    }

    /**
     * @test
     */
    public function toArray_eventWithTwoProperties_returnsArrayWithTwoProperties()
    {
        $events = [
            new StateChanged(['foo' => 'bar', 'bar' => 'foobar'])
        ];

        $this->assertSame(['foo' => 'bar', 'bar' => 'foobar'], $this->instance($events)->toArray());
    }

    /**
     * @test
     */
    public function toArray_twoEvents_returnsArrayWithUpdatedProperty()
    {
        $events = [
            new StateChanged(['foo' => 'bar']),
            new StateChanged(['foo' => 'baz'])
        ];

        $this->assertSame(['foo' => 'baz'], $this->instance($events)->toArray());
    }

    // TODO: unset properties

    /**
     * @param array $events
     * @return StateChangedProjector
     */
    private function instance(array $events = []): StateChangedProjector
    {
        $result = new StateChangedProjector();
        $result->replay(MemoryEventStream::fromArray($events));
        return $result;
    }
}