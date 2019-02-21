<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStreamFactory;
use mad654\eventstore\example\LightSwitch;
use mad654\eventstore\FileEventStream\FileEventStream;
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
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

        $subject = new LightSwitch('some-subject-id');

        $this->instance()->attach($subject);
    }

    /**
     * @test
     */
    public function attach_always_createsInternalEventForMetaInformations()
    {
        $stream = FileEventStream::new($this->rootDirPath(), 'some-subject-id');
        $this->streamFactory
            ->method('new')
            ->willReturn($stream);

        $this->instance()->attach(new LightSwitch('some-subject-id'));
        $streamData = iterator_to_array($stream->getIterator());

        $this->assertInstanceOf(ObjectCreatedEvent::class, $streamData[0]);
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

        $this->instance()->attach(new LightSwitch('some-subject-id'));

        $this->assertCount(2, iterator_to_array($stream->getIterator()));
    }

    /**
     * @test
     *
     * fixme: test depends on TestSubject::attach implementation
     */
    public function attach_changesAfterAttach_trackedAutomatically()
    {
        $store = $this->instance(new FileEventStreamFactory($this->rootDirPath()));
        $expected = new LightSwitch('initial-id');
        $store->attach($expected);

        $expected->switchKitchenOn();

        $acutal = $store->get('initial-id');
        if (!$acutal instanceof LightSwitch) {
            $this->fail("Actual instance of TestSubject");
        }

        $this->assertCount(3, $acutal->events);
        $this->assertTrue($acutal->isKitchenOn());
    }

    /**
     * @test
     */
    public function get_subjectIdKnown_returnsEqualSubjectWithoutCallingConstructor()
    {
        $expected = new LightSwitch('one');
        $expected->constructorInvocationCount = 0;
        $store = $this->instance(new FileEventStreamFactory($this->rootDirPath()));
        $store->attach($expected);

        $actual = $store->get('one');

        $expected->events = null;
        if ($actual instanceof LightSwitch) {
            $actual->events = null;
        }

        $this->assertEquals($expected, $actual);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Object with id `unknown` not found
     */
    public function get_subjectIdUnknown_throwsException()
    {
        $this->instance(new FileEventStreamFactory($this->rootDirPath()))->get('unknown');
    }

    /**
     * @test
     * fixme: test depends on TestSubject::replay implementation
     */
    public function get_changesAfterLoad_trackedAutomatically()
    {
        $store = $this->instance(new FileEventStreamFactory($this->rootDirPath()));
        $store->attach(new LightSwitch('initial-id'));
        $expected = $store->get('initial-id');
        if (!$expected instanceof LightSwitch) {
            $this->fail("Expected instance of TestSubject");
        }

        $expected->switchKitchenOn();

        $acutal = $store->get('initial-id');
        if (!$acutal instanceof LightSwitch) {
            $this->fail("Expected instance of TestSubject");
        }

        $this->assertCount(3, $acutal->events);
        $this->assertTrue($acutal->isKitchenOn());
    }

    public function instance(FileEventStreamFactory $factory = null): EventObjectStore
    {
        if (is_null($factory)) {
            $factory = $this->streamFactory;
        }

        return new EventObjectStore($factory);
    }

}
