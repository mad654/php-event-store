<?php

namespace mad654\eventstore;


interface EventStreamFactory
{
    public function new(string $id): EventStorable;
}