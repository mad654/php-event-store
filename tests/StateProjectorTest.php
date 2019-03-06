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
    public function get_always_returnsNull()
    {
        $projector = $this->instance([]);

        $this->assertNull($projector->get('foo'));
    }

    /**
     * @test
     */
    public function get_keyNotExistsWithDefault_returnsDefault()
    {
        $projector = $this->instance([]);

        $this->assertEquals('bar', $projector->get('foo', 'bar'));
    }

    /**
     * @test
     */
    public function get_keysExist_returnsValue()
    {
        $subjectId = new StringSubjectId('test');
        $payload = ['foo' => 'bar', 'foz' => 'baz'];
        $event = new StateChanged($subjectId, $payload);
        $projector = $this->instance([$event]);

        $this->assertSame('bar', $projector->get('foo'));
        $this->assertSame('baz', $projector->get('foz'));
    }

    /**
     * @test
     */
    public function projection_eventWithOneProperty_returnsArrayWithOneProperty()
    {
        $events = [
            new StateChanged(StringSubjectId::fromString('some-id'), ['foo' => 'bar'])
        ];

        $actual = $this->instance($events)->projection();

        $this->assertSame(['foo' => 'bar'], $actual);
    }

    /**
     * @test
     */
    public function projection_eventWithTwoProperties_returnsArrayWithTwoProperties()
    {
        $events = [
            new StateChanged(StringSubjectId::fromString('some-id'), ['foo' => 'bar', 'bar' => 'foobar'])
        ];

        $actual = $this->instance($events)->projection();

        $this->assertSame(['foo' => 'bar', 'bar' => 'foobar'], $actual);
    }

    /**
     * @test
     */
    public function projection_twoEvents_returnsArrayWithUpdatedProperty()
    {
        $id = StringSubjectId::fromString('some-id');
        $events = [
            new StateChanged($id, ['foo' => 'bar']),
            new StateChanged($id, ['foo' => 'baz'])
        ];

        $actual = $this->instance($events)->projection();

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

    /**
     * @test
     */
    public function intermediateIterator_oneEventOneProperty_returnsOneStateOneProperty()
    {
        $subjectId = StringSubjectId::fromString('some-id');
        $events = [
            ObjectCreatedEvent::for(new LightSwitch($subjectId)),
            new StateChanged($subjectId, ['foo' => 'bar'])
        ];

        $iterator = StateProjector::intermediateIterator(MemoryEventStream::fromArray($events));
        $actualStates = iterator_to_array($iterator);

        $this->assertCount(1, $actualStates);
        $actual = $actualStates[0];

        $this->assertNotNull($actual->lastEventTimestamp());
        $this->assertSame('bar', $actual->get('foo'));
        $this->assertSame('StateChanged', $actual->lastEventType());
        $this->assertSame('some-id', $actual->subjectId()->__toString());
        $this->assertSame(LightSwitch::class, $actual->subjectType());
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
        $actualStates = iterator_to_array($iterator);

        $this->assertSame('bar', $actualStates[0]->get('foo'));
        $this->assertSame('baz', $actualStates[1]->get('foo'));
    }
}