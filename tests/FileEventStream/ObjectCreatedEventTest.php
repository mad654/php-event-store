<?php

namespace mad654\eventstore;


use mad654\eventstore\example\LightSwitch;
use PHPUnit\Framework\TestCase;

class ObjectCreatedEventTest extends TestCase
{
    /**
     * @test
     */
    public function createFor_always_createInstance()
    {
        $actual = ObjectCreatedEvent::for(new LightSwitch(StringSubjectId::fromString('foo')));

        $this->assertInstanceOf(ObjectCreatedEvent::class, $actual);
        $this->assertInstanceOf(Event::class, $actual);
    }

    /**
     * @test
     */
    public function payload_always_containsValidClassName()
    {
        $event = ObjectCreatedEvent::for(new LightSwitch(StringSubjectId::fromString('foo')));

        $actual = $event->payload();

        $this->assertEquals(['class_name' => LightSwitch::class], $actual);
    }

    /**
     * @test
     */
    public function serializeDeserialize_subclassOfStateChanged_keepsTimestamp()
    {
        $switch = new LightSwitch(new StringSubjectId('foo'));
        $event = ObjectCreatedEvent::for($switch);

        /* @var \mad654\eventstore\ObjectCreatedEvent $actual */
        $serialized = serialize($event);
        $actual = unserialize($serialized);

        $this->assertNotNull($actual->timestamp());
        $this->assertEquals($event->timestamp(), $actual->timestamp());
    }
}
