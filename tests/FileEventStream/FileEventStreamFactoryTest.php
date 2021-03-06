<?php

namespace mad654\eventstore\FileEventStream;


use mad654\eventstore\Event\StateChanged;
use mad654\eventstore\EventStream\EventStreamFactory;
use mad654\eventstore\StringSubjectId;
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

    /**
     * @test
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Root directory not exists: ´/var/not-existing-dir´ - mount failed?
     */
    public function construct_rootDirectoryNotExists_throwsException()
    {
        new FileEventStreamFactory('/var/not-existing-dir');
    }

    /**
     * @test
     */
    public function construct_rootDirectoryNotWritable_throwsException()
    {
        $rootDir = $this->rootDirPath() . DIRECTORY_SEPARATOR . 'not-writeable';
        mkdir($rootDir, 0555);

        try {
            new FileEventStreamFactory($rootDir);
        } catch (\RuntimeException $e) {
            $this->assertStringStartsWith(
                'Root directory not writable: ´',
                $e->getMessage()
            );

            return;
        } finally {
            chmod($rootDir, 0777);
        }

        $this->fail("Expected RuntimeException");
    }

    /**
     * @test
     */
    public function construct_rootDirectoryNotADirectory_throwsException()
    {
        $rootDir = $this->rootDirPath() . DIRECTORY_SEPARATOR . 'file';
        touch($rootDir);

        try {
            new FileEventStreamFactory($rootDir);
        } catch (\RuntimeException $e) {
            $this->assertStringStartsWith(
                'Root directory not a directory: ´',
                $e->getMessage()
            );

            return;
        } finally {
            chmod($rootDir, 0777);
        }

        $this->fail("Expected RuntimeException");
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
     * @expectedException \RuntimeException
     * @expectedExceptionMessage Stream with id `existing-id` already exists
     */
    public function new_fileExists_throwsException()
    {
        $this->instance()->new('existing-id');
        $this->instance()->new('existing-id');
    }

    /**
     * @test
     */
    public function get_always_returnsExistingStreamInstance()
    {
        $factory = $this->instance();
        $expected = $factory->new('some-id')
            ->append(new StateChanged(StringSubjectId::fromString('some-id'), ['name' => 'one']));

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

    /**
     * @test
     */
    public function get_newFactoryInstance_returnsStreamInstanceWithEqualEvents()
    {
        $subjectId = StringSubjectId::fromString('some-id');
        $expected = $this->instance()->new('some-id')
            ->append(new StateChanged($subjectId, ['name' => 'one']))
            ->append(new StateChanged($subjectId, ['name' => 'two']));

        $actual = $this->instance()->get('some-id');

        $expectedEvents = iterator_to_array($expected->getIterator());
        $actualEvents = iterator_to_array($actual->getIterator());

        $this->assertEquals($expectedEvents, $actualEvents);
    }
}