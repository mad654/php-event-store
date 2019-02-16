<?php

namespace mad654\eventstore;


use Consolidation\Log\Logger;
use Symfony\Component\Console\Output\ConsoleOutput;

class FileEventStreamTest extends FileTestCase
{
    # const ConsoleLogVerbosity = ConsoleOutput::VERBOSITY_DEBUG;
    const ConsoleLogVerbosity = ConsoleOutput::VERBOSITY_NORMAL;

    /**
     * @param string $name
     * @return FileEventStream
     */
    private function instance(string $name = "test_storage"): FileEventStream
    {
        $fileEventStream = new FileEventStream($this->rootDirPath(), $name);
        $fileEventStream->attachLogger(new Logger(new ConsoleOutput(self::ConsoleLogVerbosity)));
        return $fileEventStream;
    }

    /**
     * @test
     */
    public function __construct_always_returnsEventStorable()
    {
        $this->assertInstanceOf(EventStorable::class, $this->instance());
    }

    /**
     * @test
     */
    public function __construct_always_returnsEventTraversable()
    {
        $this->assertInstanceOf(EventTraversable::class, $this->instance());
    }

    /**
     * @test
     */
    public function getIterator_always_returnsEvents()
    {
        $actual = $this->instance()
            ->append(new TestEvent('one'));

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
            ->append(new TestEvent('one'))
            ->append(new TestEvent('two'));

        $actual = iterator_to_array($actual->getIterator());

        $this->assertCount(2, $actual);
        $this->assertEquals(new TestEvent("one"), $actual[0]);
        $this->assertEquals(new TestEvent("two"), $actual[1]);
    }

    /**
     * @test
     */
    public function sut_always_persistsAddedEvents()
    {
        $expected = $this->instance();
        $expected->append(new TestEvent("one"));
        unset($expected);

        $expected = $this->instance();
        $expected->append(new TestEvent("two"));

        $actual = $this->instance();

        $this->assertEquals(
            iterator_to_array($expected->getIterator()),
            iterator_to_array($actual->getIterator())
        );
    }

    // TODO assert events ordered ascending by timestamp even if they were attached in different order
}