<?php

namespace mad654\eventstore\MemoryEventStream;


use mad654\eventstore\Event;
use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\StringSubjectId;
use PHPUnit\Framework\TestCase;

class MemoryEventStreamTest extends TestCase
{
    /**
     * @test
     */
    public function __construct_always_isEventStorable()
    {
        $this->assertInstanceOf(EventStream::class, $this->instance());
    }

    public function instance(): MemoryEventStream
    {
        return new MemoryEventStream();
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Element at index 0 does not implement Event
     */
    public function fromArray_hasNonEventElements_throwsException()
    {
        MemoryEventStream::fromArray(['string']);
    }

    /**
     * @test
     */
    public function getIterator_always_returnsEvents()
    {
        $subjectId = StringSubjectId::fromString('some-id');
        $actual = $this->instance()
            ->append(new StateChanged($subjectId, ['name' => 'one']));

        $actual = iterator_to_array($actual->getIterator());

        foreach ($actual as $item) {
            $this->assertInstanceOf(Event::class, $item);
        }
    }

    /**
     * @test
     */
    public function getIterator_twoElements_returnsIteratorWithEqualTwoElements()
    {
        $subjectId = StringSubjectId::fromString('some-id');
        $event1 = new StateChanged($subjectId, ['name' => 'one']);
        $event2 = new StateChanged($subjectId, ['name' => 'two']);
        $actual = $this->instance()
            ->append($event1)
            ->append($event2);

        $actual = iterator_to_array($actual->getIterator());

        $this->assertCount(2, $actual);
        $this->assertEquals($event1, $actual[0]);
        $this->assertEquals($event2, $actual[1]);
    }

    /**
     * @test
     */
    public function importFrom_bothOneElement_hasTwoElements()
    {
        $subjectId = StringSubjectId::fromString('some-id');

        $stream1 = $this->instance();
        $stream1->append(new StateChanged($subjectId, ['name' => 'one']));
        $stream2 = $this->instance();
        $stream2->append(new StateChanged($subjectId, ['name' => 'two']));

        $stream1->appendAll($stream2);

        $this->assertCount(2, iterator_to_array($stream1->getIterator()));
    }
}