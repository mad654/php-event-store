<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStreamEmitter;
use mad654\eventstore\EventStream\EventStreamRenderer;

interface EventSourcedObject extends EventStreamEmitter, EventStreamConsumer
{
    public function history(EventStreamRenderer $renderer): EventStreamRenderer;
}