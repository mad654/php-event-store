<?php

namespace mad654\eventstore\FileEventStream;


use Consolidation\Log\Logger;
use mad654\eventstore\Event;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventTraversable;
use mad654\eventstore\Fixtures\TestEvent;
use mad654\eventstore\TestCase\FileTestCase;
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
        $fileEventStream = FileEventStream::new($this->rootDirPath(), $name);
        $fileEventStream->attachLogger(new Logger(new ConsoleOutput(self::ConsoleLogVerbosity)));
        return $fileEventStream;
    }

    /**
     * @param string $name
     * @return FileEventStream
     */
    private function loadInstance(string $name = "test_storage"): FileEventStream
    {
        $fileEventStream = FileEventStream::load($this->rootDirPath(), $name);
        $fileEventStream->attachLogger(new Logger(new ConsoleOutput(self::ConsoleLogVerbosity)));
        return $fileEventStream;
    }

    /**
     * @test
     */
    public function new_always_returnsEventStorable()
    {
        $this->assertInstanceOf(EventStream::class, $this->instance());
    }

    /**
     * @test
     */
    public function new_always_returnsEventTraversable()
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
    public function getIterator_emptyStream_doesNotThrowException()
    {
        FileEventStream::new($this->rootDirPath(), 'empty');

        $acutal = FileEventStream::load($this->rootDirPath(), 'empty');

        foreach ($acutal->getIterator() as $item) {
            $this->fail("should not have any items but got: " . serialize($item));
        }

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function importFrom_bothOneElement_hasTwoElements()
    {
        $stream1 = $this->instance('one');
        $stream1->append(new TestEvent('one'));
        $stream2 = $this->instance('two');
        $stream2->append(new TestEvent('two'));

        $stream1->appendAll($stream2);

        $this->assertCount(2, iterator_to_array($stream1->getIterator()));
    }

    /**
     * @test
     */
    public function sut_always_persistsAddedEvents()
    {
        $expected = $this->instance();
        $expected->append(new TestEvent("one"));
        unset($expected);

        $expected = $this->loadInstance();
        $expected->append(new TestEvent("two"));

        $actual = $this->loadInstance();

        $this->assertEquals(
            iterator_to_array($expected->getIterator()),
            iterator_to_array($actual->getIterator())
        );
    }
}
