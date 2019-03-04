<?php

namespace mad654\eventstore\EventStream;


interface EventStreamRenderer
{
    public function render(EventStream $events): void;
}