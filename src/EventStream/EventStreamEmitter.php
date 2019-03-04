<?php

namespace mad654\eventstore\EventStream;


interface EventStreamEmitter
{
    public function subjectId(): string;

    public function emitEventsTo(EventStream $stream);
}