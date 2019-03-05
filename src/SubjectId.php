<?php

namespace mad654\eventstore;


interface SubjectId extends \Serializable
{
    public static function fromString(string $id): SubjectId;

    public function __toString(): string;
}