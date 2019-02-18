<?php

namespace mad654\eventstore;


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
        $stream = new FileEventStream($this->rootDirPath(), 'some-subject-id');
        $this->streamFactory
            ->method('new')
            ->willReturn($stream);

        $this->instance()->attach(new TestSubject('some-subject-id'));

        $this->assertCount(1, iterator_to_array($stream->getIterator()));
    }

    /**
     * @test
     */
    public function get_subjectIdKnown_returnsSameSubject()
    {
        $expected = new TestSubject('one');
        $store = $this->instance();
        $store->attach($expected);

        $actual = $store->get('one');

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Object with id `unknown` not found
     */
    public function get_subjectIdUnknown_throwsException()
    {
        $this->instance()->get('unknown');
    }

    /**
     * @return EventObjectStore
     */
    public function instance(): EventObjectStore
    {
        return new EventObjectStore($this->streamFactory);
    }

}
