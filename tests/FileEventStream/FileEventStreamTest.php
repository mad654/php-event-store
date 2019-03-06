<?php

namespace mad654\eventstore\FileEventStream;


use Consolidation\Log\Logger;
use mad654\eventstore\Event;
use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventTraversable;
use mad654\eventstore\StringSubjectId;
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
            ->append(new StateChanged(StringSubjectId::fromString('some-id'), ['name' => 'one']));

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
        $subjectId = StringSubjectId::fromString('some-id');

        $stream1 = $this->instance('one');
        $stream1->append(new StateChanged($subjectId, ['name' => 'one']));
        $stream2 = $this->instance('two');
        $stream2->append(new StateChanged($subjectId, ['name' => 'two']));

        $stream1->appendAll($stream2);

        $this->assertCount(2, iterator_to_array($stream1->getIterator()));
    }

    /**
     * @test
     */
    public function sut_always_persistsAddedEvents()
    {
        $subjectId = StringSubjectId::fromString('some-id');
        $expected = $this->instance();
        $expected->append(new StateChanged($subjectId, ['name' => 'one']));
        unset($expected);

        $expected = $this->loadInstance();
        $expected->append(new StateChanged($subjectId, ['name' => 'two']));

        $actual = $this->loadInstance();

        $expectedData = iterator_to_array($expected->getIterator());
        $actualData = iterator_to_array($actual->getIterator());

        $this->assertCount(2, $expectedData);
        $this->assertCount(2, $actualData);
        $this->assertEquals($expectedData, $actualData);
    }

    /**
     * @test
     */
    public function sut_afterUnserialize_canAppendEventsToFile()
    {
        $subjectId = StringSubjectId::fromString('some-id');
        $expected = $this->instance();
        $expected->append(new StateChanged($subjectId, ['name' => 'one']));

        $serialized = serialize($expected);
        $unserialized = unserialize($serialized);
        $unserialized->append(new StateChanged($subjectId, ['name' => 'two']));
        unset($unserialized);

        $actual = $this->loadInstance();
        $actualData = iterator_to_array($actual->getIterator());

        $this->assertCount(2, $actualData);
    }
}
