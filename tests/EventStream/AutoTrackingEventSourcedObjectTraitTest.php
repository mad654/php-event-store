<?php

namespace mad654\eventstore\EventStream;


use mad654\eventstore\example\LightSwitch;
use mad654\eventstore\StringSubjectId;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AutoTrackingEventSourcedObjectTraitTest extends TestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function history_always_callsRenderOnRenderer()
    {
        $subject = new LightSwitch(StringSubjectId::fromString('foo'));

        /* @var EventStreamRenderer|MockObject $renderer */
        $renderer = $this->getMockForAbstractClass(EventStreamRenderer::class);
        $renderer->expects($this->once())
            ->method('render')
            ->with($subject->events);

        $subject->history($renderer);
    }
}
