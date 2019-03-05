<?php

namespace mad654\eventstore;


use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\example\LightSwitch;
use mad654\eventstore\MemoryEventStream\MemoryEventStream;
use PHPUnit\Framework\TestCase;

class StateProjectorTest extends TestCase
{
    /**
     * @test
     */
    public function __construct_always_returnsEventConsumer()
    {
        $this->assertInstanceOf(EventStreamConsumer::class, $this->instance());
    }

    /**
     * @param array $events
     * @return StateProjector
     */
    private function instance(array $events = []): StateProjector
    {
        $result = new StateProjector();
        $result->replay(MemoryEventStream::fromArray($events));
        return $result;
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
            new StateChanged('some-id', ['foo' => 'bar'])
        ];

        $this->assertSame(['foo' => 'bar'], $this->instance($events)->toArray());
    }

    /**
     * @test
     */
    public function toArray_eventWithTwoProperties_returnsArrayWithTwoProperties()
    {
        $events = [
            new StateChanged('some-id', ['foo' => 'bar', 'bar' => 'foobar'])
        ];

        $this->assertSame(['foo' => 'bar', 'bar' => 'foobar'], $this->instance($events)->toArray());
    }

    /**
     * @test
     */
    public function toArray_twoEvents_returnsArrayWithUpdatedProperty()
    {
        $events = [
            new StateChanged('some-id', ['foo' => 'bar']),
            new StateChanged('some-id', ['foo' => 'baz'])
        ];

        $this->assertSame(['foo' => 'baz'], $this->instance($events)->toArray());
    }

    /**
     * @test
     */
    public function intermediateIterator_always_returnsIterator()
    {
        $actual = StateProjector::intermediateIterator(new MemoryEventStream());

        $this->assertInstanceOf(\Iterator::class, $actual);
    }

    // TODO intermediateIterator has __meta['subject']['id']
    // TODO intermediateIterator has __meta['subject']['class']

    /**
     * @test
     */
    public function intermediateIterator_oneEventOneProperty_returnsOneArrayOneProperty()
    {
        $events = [
            ObjectCreatedEvent::for(new LightSwitch('some-id')),
            new StateChanged('some-id', ['foo' => 'bar'])
        ];

        $iterator = StateProjector::intermediateIterator(MemoryEventStream::fromArray($events));

        $actual = iterator_to_array($iterator);
        $this->assertCount(1, $actual);
        $this->assertSame('bar', $actual[0]['foo']);
        $this->assertArrayHasKey('__meta', $actual[0]);
        $this->assertArrayHasKey('timestamp', $actual[0]['__meta']);
        $this->assertSame('StateChanged', $actual[0]['__meta']['type']);
        $this->assertSame('some-id', $actual[0]['__meta']['subject']['id']);
        $this->assertSame(LightSwitch::class, $actual[0]['__meta']['subject']['type']);

    }

    // TODO: unset properties

    /**
     * @test
     */
    public function intermediateIterator_twoEventsOneProperty_returnsStateAfterEachEvent()
    {
        $events = [
            new StateChanged('some-id', ['foo' => 'bar']),
            new StateChanged('some-id', ['foo' => 'baz'])
        ];

        $iterator = StateProjector::intermediateIterator(MemoryEventStream::fromArray($events));
        $actual = iterator_to_array($iterator);

        $this->assertSame('bar', $actual[0]['foo']);
        $this->assertSame('baz', $actual[1]['foo']);
    }
}