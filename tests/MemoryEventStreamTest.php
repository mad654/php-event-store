<?php

namespace mad654\eventstore;


use PHPUnit\Framework\TestCase;

class MemoryEventStreamTest extends TestCase
{
    /**
     * @test
     */
    public function __construct_always_isEventStorable()
    {
        $this->assertInstanceOf(EventStorable::class, $this->instance());
    }

    public function instance(): MemoryEventStream
    {
        return new MemoryEventStream();
    }

    /**
     * @test
     */
    public function getIterator_always_returnsEvents()
    {
        $actual = $this->instance()
            ->attach(new TestEvent('one'));

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
        $actual = $this->instance()
            ->attach(new TestEvent('one'))
            ->attach(new TestEvent('two'));

        $actual = iterator_to_array($actual->getIterator());

        $this->assertCount(2, $actual);
        $this->assertEquals(new TestEvent("one"), $actual[0]);
        $this->assertEquals(new TestEvent("two"), $actual[1]);
    }

    /**
     * @test
     */
    public function importFrom_bothOneElement_hasTwoElements()
    {
        $stream1 = $this->instance();
        $stream1->attach(new TestEvent('one'));
        $stream2 = $this->instance();
        $stream2->attach(new TestEvent('two'));

        $stream1->importAll($stream2);

        $this->assertCount(2, iterator_to_array($stream1->getIterator()));
    }
}