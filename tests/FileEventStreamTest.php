<?php

namespace mad654\eventstore;


class FileEventStreamTest extends FileTestCase
{

    /**
     * @test
     */
    public function __construct_always_returnsEventStorable()
    {
        $this->assertInstanceOf(EventStorable::class, $this->instance());
    }

    /**
     * @return FileEventStream
     */
    public function instance(): FileEventStream
    {
        return new FileEventStream();
    }

    /**
     * @test
     */
    public function __construct_always_returnsEventTraversable()
    {
        $this->assertInstanceOf(EventTraversable::class, $this->instance());
    }

    /**
     * @test
     */
    public function sut_always_persistsAddedEvents()
    {
        $expected = $this->instance();
        $expected->append(new TestEvent());

        $actual = $this->instance()->load();

        $this->assertEquals($expected, $actual);
    }
}
