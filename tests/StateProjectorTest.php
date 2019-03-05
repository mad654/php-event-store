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
        $actual = $this->instance()->toArray();
        unset($actual['__meta']);

        $this->assertSame([], $actual);
    }

    /**
     * @test
     */
    public function toArray_eventWithOneProperty_returnsArrayWithOneProperty()
    {
        $events = [
            new StateChanged(StringSubjectId::fromString('some-id'), ['foo' => 'bar'])
        ];

        $actual = $this->instance($events)->toArray();
        unset($actual['__meta']);

        $this->assertSame(['foo' => 'bar'], $actual);
    }

    /**
     * @test
     */
    public function toArray_eventWithTwoProperties_returnsArrayWithTwoProperties()
    {
        $events = [
            new StateChanged(StringSubjectId::fromString('some-id'), ['foo' => 'bar', 'bar' => 'foobar'])
        ];

        $actual = $this->instance($events)->toArray();
        unset($actual['__meta']);

        $this->assertSame(['foo' => 'bar', 'bar' => 'foobar'], $actual);
    }

    /**
     * @test
     */
    public function toArray_twoEvents_returnsArrayWithUpdatedProperty()
    {
        $id = StringSubjectId::fromString('some-id');
        $events = [
            new StateChanged($id, ['foo' => 'bar']),
            new StateChanged($id, ['foo' => 'baz'])
        ];

        $actual = $this->instance($events)->toArray();
        unset($actual['__meta']);

        $this->assertSame(['foo' => 'baz'], $actual);
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
        $subjectId = StringSubjectId::fromString('some-id');
        $events = [
            ObjectCreatedEvent::for(new LightSwitch($subjectId)),
            new StateChanged($subjectId, ['foo' => 'bar'])
        ];

        $iterator = StateProjector::intermediateIterator(MemoryEventStream::fromArray($events));

        // FIXME: cleanup toArray
        $actual = iterator_to_array($iterator);
        $this->assertCount(1, $actual);
        $this->assertSame('bar', $actual[0]->toArray()['foo']);
        $this->assertArrayHasKey('__meta', $actual[0]->toArray());
        $this->assertArrayHasKey('timestamp', $actual[0]->toArray()['__meta']);
        $this->assertSame('StateChanged', $actual[0]->toArray()['__meta']['type']);
        $this->assertSame('some-id', $actual[0]->toArray()['__meta']['subject']['id']->__toString());
        $this->assertSame(LightSwitch::class, $actual[0]->toArray()['__meta']['subject']['type']);

    }

    // TODO: unset properties

    /**
     * @test
     */
    public function intermediateIterator_twoEventsOneProperty_returnsStateAfterEachEvent()
    {
        $subjectId = StringSubjectId::fromString('some-id');
        $events = [
            new StateChanged($subjectId, ['foo' => 'bar']),
            new StateChanged($subjectId, ['foo' => 'baz'])
        ];

        $iterator = StateProjector::intermediateIterator(MemoryEventStream::fromArray($events));
        $actual = iterator_to_array($iterator);

        // FIXME: cleanup toArray
        $this->assertSame('bar', $actual[0]->toArray()['foo']);
        $this->assertSame('baz', $actual[1]->toArray()['foo']);
    }
}