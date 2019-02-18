<?php

namespace mad654\eventstore;


interface EventStreamEmitter
{
    public function subjectId(): string;

    public function emitEventsTo(EventStream $stream);
}