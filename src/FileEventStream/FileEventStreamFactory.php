<?php

namespace mad654\eventstore\FileEventStream;


use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\EventStream\EventStreamFactory;

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
        $stream = FileEventStream::new($this->rooDirPath, $id);
        $this->knownStreams[$id] = $stream;
        return $stream;
    }

    public function get(string $id): EventStream
    {
        if (!array_key_exists($id, $this->knownStreams)) {
            return FileEventStream::load($this->rooDirPath, $id);
        }

        return $this->knownStreams[$id];
    }
}