<?php

namespace mad654\eventstore\Event;


use Dflydev\DotAccessData\Data;
use mad654\eventstore\Event;

class StateChanged implements Event
{
    /**
     * @var Data
     */
    private $payload;

    public function __construct(array $payload)
    {
        $this->payload = new Data($payload);
    }

    public function payload(): array
    {
        return $this->payload->export();
    }

    public function has(string $key): bool
    {
        return $this->payload->has($key);
    }

    public function get(string $key, $default = null)
    {
        if (!$this->payload->has($key)) {
            return $default;
        }

        return $this->payload->get($key);
    }
}