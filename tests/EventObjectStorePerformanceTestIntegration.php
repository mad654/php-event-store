<?php

namespace mad654\eventstore;


use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\Fixtures\TestSubject;
use mad654\eventstore\TestCase\FileTestCase;

class EventObjectStorePerformanceTestIntegration extends FileTestCase
{
    /**
     * @test
     */
    public function get_singleSubjectWith10000Events_loadsIn65ms()
    {
        $store = new EventObjectStore(new FileEventStreamFactory($this->rootDirPath()));
        $subject = new TestSubject('foo');
        $store->attach($subject);

        foreach (range(1, 10000) as $i) {
            $subject->dummyEventAction($i);
        }


        $diff = take_time(function () {
            $store = new EventObjectStore(new FileEventStreamFactory($this->rootDirPath()));
            $store->get('foo');
        });

        $this->assertlessThanOrEqual(65, $diff, $diff);
    }
}