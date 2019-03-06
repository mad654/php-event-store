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
    public function get_singleSubjectWith10000Events_loadsIn100ms()
    {
        $id = StringSubjectId::fromString('foo');
        $store = new EventSourcedObjectStore(new FileEventStreamFactory($this->rootDirPath()));
        $subject = new LightSwitch($id);
        $store->attach($subject);

        foreach (range(1, 10000) as $i) {
            $subject->switchOn($i);
        }


        $diff = take_time(function () use ($id) {
            $store = new EventSourcedObjectStore(new FileEventStreamFactory($this->rootDirPath()));
            $store->get($id);
        });

        $this->assertlessThanOrEqual(100, $diff, $diff);
    }
}