<?php

namespace mad654\eventstore;


class FileEventStreamFactory implements EventStreamFactory
{

    /**
     * @var string
     */
    private $rooDirPath;

    /**
     * @var FileEventStream[]
     */
    private $knownStreams;

    public function __construct(string $rootDirPath)
    {
        $this->rooDirPath = $rootDirPath;
        $this->knownStreams = [];
    }

    public function new(string $id): EventStream
    {
        $stream = new FileEventStream($this->rooDirPath, $id);
        $this->knownStreams[$id] = $stream;
        return $stream;
    }

    public function get(string $id): EventStream
    {
        if (!array_key_exists($id, $this->knownStreams)) {
            return new FileEventStream($this->rooDirPath, $id);
        }

        return $this->knownStreams[$id];
    }
}