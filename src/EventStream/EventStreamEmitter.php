<?php

namespace mad654\eventstore\EventStream;


use mad654\eventstore\SubjectId;

interface EventStreamEmitter
{
    public function subjectId(): SubjectId;

    public function emitEventsTo(EventStream $stream);
}