<?php

namespace mad654\eventstore;


final class StringSubjectId implements SubjectId
{
    private $id = '';

    /**
     * SubjectId constructor.
     * @param $id
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public static function fromString(string $id): SubjectId
    {
        return new self($id);
    }

    /**
     * String representation of object
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        return $this->id;
    }

    /**
     * Constructs the object
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        $this->id = $serialized;
    }
}