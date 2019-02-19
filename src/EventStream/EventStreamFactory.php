<?php

namespace mad654\eventstore\EventStream;


interface EventStreamFactory
{
    public function new(string $id): EventStream;

    public function get(string $id): EventStream;
}