<?php

namespace mad654\eventstore;


interface Event
{

    public function serialize(): string;

    public static function deserialize(string $serialized): Event;
}