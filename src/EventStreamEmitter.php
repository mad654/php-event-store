<?php

namespace mad654\eventstore;


interface EventStreamEmitter
{
    public function subjectId(): string;

    // TODO return EventStream
    public function events(): array;
}