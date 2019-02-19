<?php

namespace mad654\eventstore;

/**
 * Class Event
 * @package mad654\eventstore
 *
 */
interface Event
{
    public function payload(): array;
}