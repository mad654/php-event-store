<?php

namespace mad654\eventstore;


use mad654\eventstore\FileEventStream\FileEventStream;
use mad654\eventstore\Fixtures\TestEvent;
use mad654\eventstore\TestCase\FileTestCase;

class FileEventStreamPerformanceTestIntegration extends FileTestCase
{
    /**
     * @test
     * @throws \Exception
     */
    public function iterateAllEvents_singleFile10000Events_loadsIn50ms()
    {
        $stream = $this->newInstance();

        foreach (range(1, 10000) as $iteration) {
            $stream->append(new TestEvent($iteration));
        }

        $actual = $this->loadInstance();

        $diff = take_time(function () use ($actual) {
            $count = 0;
            foreach ($actual as $event) {
                $count++;
            }
        });

        $this->assertlessThanOrEqual(50, $diff, $diff);
    }

    /**
     * @return FileEventStream
     */
    public function newInstance(): FileEventStream
    {
        $stream = FileEventStream::new(
            $this->rootDirPath(),
            'sut_singleFile1000Events_loadsIn100ms'
        );
        return $stream;
    }

    /**
     * @return FileEventStream
     */
    public function loadInstance(): FileEventStream
    {
        $stream = FileEventStream::load(
            $this->rootDirPath(),
            'sut_singleFile1000Events_loadsIn100ms'
        );
        return $stream;
    }
}