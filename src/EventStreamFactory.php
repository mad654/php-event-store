<?php

namespace mad654\eventstore;


interface EventStreamFactory
{
    public function new(string $id): EventStream;

    public function get(string $id): EventStream;
}