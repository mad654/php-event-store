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
    public function sut_singleFile100000Events_loadsIn200ms()
    {
        $stream = $this->instance();

        foreach (range(1, 100000) as $iteration) {
            $stream->append(new TestEvent($iteration));
        }

        $actual = $this->instance();

        $diff = $this->takeTime(function () use ($actual) {
            $count = 0;
            foreach ($actual as $event) {
                $count++;
            }
        });

        $this->assertLessThanOrEqual(200, $diff);
    }

    /**
     * @return FileEventStream
     */
    public function instance(): FileEventStream
    {
        $stream = new FileEventStream(
            $this->rootDirPath(),
            'sut_singleFile1000Events_loadsIn100ms'
        );
        return $stream;
    }

    private function takeTime(\Closure $param): float
    {
        $start = microtime(true);
        call_user_func($param);
        $stop = microtime(true);

        return round(($stop - $start) * 1000, 2);
    }
}