<?php

namespace mad654\eventstore;


use mad654\eventstore\EventStream\EventStream;

interface EventStreamConsumer
{
    public function replay(EventStream $stream): void;
}