<?php

namespace mad654\eventstore\Event;


use Dflydev\DotAccessData\Data;
use mad654\eventstore\Event;

class StateChanged implements Event
{
    /**
     * @var \DateTimeImmutable
     */
    private $timestamp;

    /**
     * @var Data
     */
    private $payload;

    /**
     * @var string
     * @TODO refactor to ID Object
     */
    private $subjectId;

    public function __construct(string $subjectId, array $payload)
    {
        try {
            $this->timestamp = new \DateTimeImmutable();
        } catch (\Exception $e) {
            throw new \RuntimeException(
                "Could not create timestamp for event",
                1,
                $e
            );
        }

        $this->payload = new Data($payload);
        $this->subjectId = $subjectId;
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

    public function timestamp(): \DateTimeImmutable
    {
        return $this->timestamp;
    }

    public function subjectId(): string
    {
        return $this->subjectId;
    }
}