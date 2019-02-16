<?php

namespace mad654\eventstore;


class TestEvent implements Event
{
    /**
     * @var string
     */
    private $someEventField;

    /**
     * TestEvent constructor.
     * @param string $someEventField
     */
    public function __construct(string $someEventField)
    {
        $this->someEventField = $someEventField;
    }

    public static function deserialize(string $serialized): Event
    {
        $data = json_decode($serialized, true);

        $instance = new static("");
        foreach ($instance as $property => $value) {
            if (array_key_exists($property, $data)) {
                $instance->$property = $data[$property];
            }
        }

        return $instance;
    }

    public function serialize(): string
    {
        return json_encode([
            "someEventField" => $this->someEventField,
            "type" => self::class
        ]);
    }
}