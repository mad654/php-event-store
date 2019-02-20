<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStreamFactory;
use mad654\eventstore\FileEventStream\FileEventStream;
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\Fixtures\TestSubject;
use mad654\eventstore\TestCase\FileTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class EventObjectStoreTest extends FileTestCase
{
    /**
     * @var EventStreamFactory|MockObject
     */
    private $streamFactory;

    /**
     * @throws \ReflectionException
     */
    protected function setUp()
    {
        parent::setUp();
        $this->streamFactory = $this->getMockForAbstractClass(EventStreamFactory::class);
    }

    /**
     * @test
     */
    public function attach_always_createsNewStream()
    {
        $this->streamFactory
            ->expects($this->once())
            ->method('new')
            ->with('some-subject-id');

        $subject = new TestSubject('some-subject-id');

        $this->instance()->attach($subject);
    }

    /**
     * @test
     */
    public function attach_always_fwdEventsToNewStream()
    {
        $stream = FileEventStream::new($this->rootDirPath(), 'some-subject-id');
        $this->streamFactory
            ->method('new')
            ->willReturn($stream);

        $this->instance()->attach(new TestSubject('some-subject-id'));

        $this->assertCount(1, iterator_to_array($stream->getIterator()));
    }

    /**
     * @test
     */
    public function get_subjectIdKnown_returnsEqualSubjectWithoutCallingConstructor()
    {
        $expected = new TestSubject('one');
        $expected->constructorInvocationCount = 0;
        $store = $this->instance(new FileEventStreamFactory($this->rootDirPath()));
        $store->attach($expected);

        $actual = $store->get('one');

        $this->assertEquals($expected, $actual);
    }

    # TODO: reply not call constructor

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Object with id `unknown` not found
     */
    public function get_subjectIdUnknown_throwsException()
    {
        $this->instance(new FileEventStreamFactory($this->rootDirPath()))->get('unknown');
    }

    public function instance(FileEventStreamFactory $factory = null): EventObjectStore
    {
        if (is_null($factory)) {
            $factory = $this->streamFactory;
        }

        return new EventObjectStore($factory);
    }

}
