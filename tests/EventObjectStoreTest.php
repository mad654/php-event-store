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

        $subject = new LightSwitch(StringSubjectId::fromString('some-subject-id'));

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

        $this->instance()->attach(new LightSwitch(StringSubjectId::fromString('some-subject-id')));
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

        $this->instance()->attach(new LightSwitch(StringSubjectId::fromString('some-subject-id')));

        $this->assertCount(2, iterator_to_array($stream->getIterator()));
    }

    /**
     * @test
     *
     * fixme: test depends on TestSubject::attach implementation
     */
    public function attach_changesAfterAttach_trackedAutomatically()
    {
        $subjectId = StringSubjectId::fromString('initial-id');
        $store = $this->instance(new FileEventStreamFactory($this->rootDirPath()));
        $expected = new LightSwitch($subjectId);
        $store->attach($expected);

        $expected->switchOn();

        $acutal = $store->get($subjectId);
        if (!$acutal instanceof LightSwitch) {
            $this->fail("Actual instance of TestSubject");
        }

        $this->assertCount(3, $acutal->events);
        $this->assertTrue($acutal->isOn());
    }

    /**
     * @test
     */
    public function get_subjectIdKnown_returnsEqualSubjectWithoutCallingConstructor()
    {
        $subjectId = StringSubjectId::fromString('one');
        $expected = new LightSwitch($subjectId);
        $expected->constructorInvocationCount = 0;
        $store = $this->instance(new FileEventStreamFactory($this->rootDirPath()));
        $store->attach($expected);

        $actual = $store->get($subjectId);

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
        $unknownId = StringSubjectId::fromString('unknown');
        $this->instance(new FileEventStreamFactory($this->rootDirPath()))->get($unknownId);
    }

    /**
     * @test
     * fixme: test depends on LightSwitch::replay implementation
     */
    public function get_changesAfterLoad_trackedAutomatically()
    {
        $subjectId = StringSubjectId::fromString('initial-id');
        $store = $this->instance(new FileEventStreamFactory($this->rootDirPath()));
        $store->attach(new LightSwitch($subjectId));
        $expected = $store->get($subjectId);
        if (!$expected instanceof LightSwitch) {
            $this->fail("Expected instance of TestSubject");
        }

        $expected->switchOn();

        $acutal = $store->get($subjectId);
        if (!$acutal instanceof LightSwitch) {
            $this->fail("Expected instance of TestSubject");
        }

        $this->assertCount(3, $acutal->events);
        $this->assertTrue($acutal->isOn());
    }

    public function instance(FileEventStreamFactory $factory = null): EventSourcedObjectStore
    {
        if (is_null($factory)) {
            $factory = $this->streamFactory;
        }

        return new EventSourcedObjectStore($factory);
    }

}
