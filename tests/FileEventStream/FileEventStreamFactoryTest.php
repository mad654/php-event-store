<?php

namespace mad654\eventstore\FileEventStream;


use mad654\eventstore\EventStream\EventStreamFactory;
use mad654\eventstore\Fixtures\TestEvent;
use mad654\eventstore\TestCase\FileTestCase;

class FileEventStreamFactoryTest extends FileTestCase
{
    /**
     * @test
     */
    public function construct_always_implementsFileEventStreamFactory()
    {
        $this->assertInstanceOf(EventStreamFactory::class, $this->instance());
    }

    public function instance(): FileEventStreamFactory
    {
        return new FileEventStreamFactory($this->rootDirPath());
    }

    /**
     * @test
     */
    public function new_always_createsNewFile()
    {
        $this->instance()->new('some-id');
        $this->assertCount(3, scandir($this->rootDirPath()));
    }

    /**
     * @test
     */
    public function get_always_returnsExistingStreamInstance()
    {
        $factory = $this->instance();
        $expected = $factory->new('some-id')
            ->append(new TestEvent('one'));

        $actual = $factory->get('some-id');

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Stream with id `unknown` not found
     */
    public function get_unknownId_throwsException()
    {
        $this->instance()->get('unknown');
    }

    // TODO construct directory not exists, throws exception
    // TODO construct directory not writeable, throws exception
    // TODO new file exists throws exception
    // TODO filesystem error throws exception

    /**
     * @test
     */
    public function get_newFactoryInstance_returnsStreamInstanceWithEqualEvents()
    {
        $expected = $this->instance()->new('some-id')
            ->append(new TestEvent('one'))
            ->append(new TestEvent('two'));

        $actual = $this->instance()->get('some-id');

        $expectedEvents = iterator_to_array($expected->getIterator());
        $actualEvents = iterator_to_array($actual->getIterator());

        $this->assertEquals($expectedEvents, $actualEvents);
    }
}