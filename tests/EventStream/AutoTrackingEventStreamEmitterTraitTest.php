<?php

namespace mad654\eventstore\EventStream;


use mad654\eventstore\example\LightSwitch;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AutoTrackingEventStreamEmitterTraitTest extends TestCase
{
    /**
     * @test
     * @throws \ReflectionException
     */
    public function history_always_callsRenderOnRenderer()
    {
        $subject = new LightSwitch('foo');

        /* @var EventStreamRenderer|MockObject $renderer */
        $renderer = $this->getMockForAbstractClass(EventStreamRenderer::class);
        $renderer->expects($this->once())
            ->method('render')
            ->with($subject->events);

        $subject->history($renderer);
    }
}
