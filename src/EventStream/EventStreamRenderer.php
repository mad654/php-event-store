<?php

namespace mad654\eventstore\EventStream;


use mad654\eventstore\Event;

interface EventStreamRenderer
{
    public function renderEvent(Event $event): void;
}