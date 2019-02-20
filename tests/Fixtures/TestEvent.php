<?php

namespace mad654\eventstore\Fixtures;


use mad654\eventstore\Event;

# TODO: rename to StateChanged
# TODO: support array of key => value pairs (tree)
# TODO: support easy access to payload values with defaults (example: GenericEvent::value($key, $default = null)
class TestEvent implements Event
{
    /**
     * @var array
     */
    private $payload;

    /**
     * TestEvent constructor.
     * @param string $someEventField
     */
    public function __construct(string $someEventField)
    {
        $this->payload = ['someEventField' => $someEventField];
    }

    public function payload(): array
    {
        return $this->payload;
    }
}