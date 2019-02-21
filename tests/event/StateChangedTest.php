<?php

namespace mad654\eventstore\event;


use mad654\eventstore\Event;
use PHPUnit\Framework\TestCase;

class StateChangedTest extends TestCase
{
    /**
     * @test
     */
    public function __construct_always_returnsInstanceOfEvent()
    {
        $this->assertInstanceOf(Event::class, new StateChanged([]));
    }

    /**
     * @test
     */
    public function payload_always_returnsArray()
    {
        $instance = new StateChanged(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $instance->payload());
    }

    /**
     * @test
     */
    public function has_always_returnsFalse()
    {
        $instance = new StateChanged([]);
        $this->assertFalse($instance->has('foo'));
    }

    /**
     * @test
     */
    public function has_keyExists_returnsTrue()
    {
        $instance = new StateChanged(['foo' => 'bar']);
        $this->assertTrue($instance->has('foo'));
    }

    /**
     * @test
     */
    public function has_pathMatchNestedArray_returnsTrue()
    {
        $instance = new StateChanged(['foo' => ['bar' => 'foobar']]);
        $this->assertTrue($instance->has('foo.bar'));
    }

    /**
     * @test
     */
    public function get_always_returnsNull()
    {
        $instance = new StateChanged([]);
        $this->assertNull($instance->get('foo'));
    }

    /**
     * @test
     */
    public function get_withDefaultKeyMissing_returnsDefault()
    {
        $instance = new StateChanged([]);
        $this->assertSame('bar', $instance->get('foo', 'bar'));
    }

    /**
     * @test
     */
    public function get_keyExists_returnsValue()
    {
        $instance = new StateChanged(['foo' => 'bar']);
        $this->assertSame('bar', $instance->get('foo'));
    }

    /**
     * @test
     */
    public function get_pathMatchNestedArray_returnsValue()
    {
        $instance = new StateChanged(['foo' => ['bar' => 'foobar']]);
        $this->assertSame('foobar', $instance->get('foo.bar'));
    }
}
