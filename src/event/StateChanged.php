<?php

namespace mad654\eventstore\event;


use mad654\eventstore\Event;

# TODO: support array of key => value pairs (tree)
# TODO: support easy access to payload values with defaults (example: GenericEvent::value($key, $default = null)
class StateChanged implements Event
{
    /**
     * @var array
     */
    private $payload;

    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public function payload(): array
    {
        return $this->payload;
    }
}