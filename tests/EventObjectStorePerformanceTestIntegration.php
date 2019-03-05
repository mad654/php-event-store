<?php

namespace mad654\eventstore;


use mad654\eventstore\example\LightSwitch;
use mad654\eventstore\FileEventStream\FileEventStreamFactory;
use mad654\eventstore\TestCase\FileTestCase;

class EventObjectStorePerformanceTestIntegration extends FileTestCase
{
    /**
     * @test
     */
    public function get_singleSubjectWith10000Events_loadsIn65ms()
    {
        $store = new EventSourcedObjectStore(new FileEventStreamFactory($this->rootDirPath()));
        $subject = new LightSwitch(StringSubjectId::fromString('foo'));
        $store->attach($subject);

        foreach (range(1, 10000) as $i) {
            $subject->switchOn($i);
        }


        $diff = take_time(function () {
            $store = new EventSourcedObjectStore(new FileEventStreamFactory($this->rootDirPath()));
            $store->get('foo');
        });

        $this->assertlessThanOrEqual(65, $diff, $diff);
    }
}