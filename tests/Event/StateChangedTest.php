<?php

namespace mad654\eventstore\Event;


use mad654\eventstore\Event;
use PHPUnit\Framework\TestCase;

class StateChangedTest extends TestCase
{
    /**
     * @test
     */
    public function __construct_always_returnsInstanceOfEvent()
    {
        $this->assertInstanceOf(Event::class, new StateChanged('some-id', []));
    }

    /**
     * @test
     */
    public function __construct_always_hasImmutableTimestamp()
    {
        $start = new \DateTimeImmutable();
        $event = new StateChanged('some-id', []);

        $actual = $event->timestamp();

        $this->assertGreaterThan($start, $actual);
        $this->assertInstanceOf(\DateTimeImmutable::class, $actual);
    }

    /**
     * @test
     */
    public function payload_always_returnsArray()
    {
        $instance = new StateChanged('some-id', ['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $instance->payload());
    }

    /**
     * @test
     */
    public function has_always_returnsFalse()
    {
        $instance = new StateChanged('some-id', []);
        $this->assertFalse($instance->has('foo'));
    }

    /**
     * @test
     */
    public function has_keyExists_returnsTrue()
    {
        $instance = new StateChanged('some-id', ['foo' => 'bar']);
        $this->assertTrue($instance->has('foo'));
    }

    /**
     * @test
     */
    public function has_pathMatchNestedArray_returnsTrue()
    {
        $instance = new StateChanged('some-id', ['foo' => ['bar' => 'foobar']]);
        $this->assertTrue($instance->has('foo.bar'));
    }

    /**
     * @test
     */
    public function get_always_returnsNull()
    {
        $instance = new StateChanged('some-id', []);
        $this->assertNull($instance->get('foo'));
    }

    /**
     * @test
     */
    public function get_withDefaultKeyMissing_returnsDefault()
    {
        $instance = new StateChanged('some-id', []);
        $this->assertSame('bar', $instance->get('foo', 'bar'));
    }

    /**
     * @test
     */
    public function get_keyExists_returnsValue()
    {
        $instance = new StateChanged('some-id', ['foo' => 'bar']);
        $this->assertSame('bar', $instance->get('foo'));
    }

    /**
     * @test
     */
    public function get_pathMatchNestedArray_returnsValue()
    {
        $instance = new StateChanged('some-id', ['foo' => ['bar' => 'foobar']]);
        $this->assertSame('foobar', $instance->get('foo.bar'));
    }
}
