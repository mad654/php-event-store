<?php

namespace mad654\eventstore;


use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Traversable;

/**
 * Class FileEventStream
 *
 * Can store/load/traverse events stored in filesystem
 * in the order they were attached originally
 *
 * @see EventStream
 * @package mad654\eventstore
 *
 * TODO Implement FileEventStreamFactory
 */
class FileEventStream implements EventStream, Logable
{
    const DELIMITER = '###\n';

    /**
     * @var string
     */
    private $rootDirPath;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $filePath;

    /**
     * @var resource
     */
    private $fileHandle;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * SubjectEventStore constructor.
     * @param string $rootDirPath
     * @param string $name
     */
    public function __construct(string $rootDirPath, string $name)
    {
        $this->filePath = $rootDirPath . DIRECTORY_SEPARATOR . $name . ".dat";
        $fh = fopen($this->filePath, 'a+');

        if ($fh === false) {
            throw new \RuntimeException("Could not open storage file: `$this->filePath`");
        }

        $this->fileHandle = $fh;
        $this->rootDirPath = $rootDirPath;
        $this->name = $name;
        $this->logger = new NullLogger();

        $this->flock(LOCK_SH);
        $this->logger->debug("opened `$this->filePath`");
    }

    /**
     * Retrieve an external iterator
     * @link https://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator()
    {
        fseek($this->fileHandle, 0);
        $content = fread($this->fileHandle, filesize($this->filePath));

        foreach (explode(self::DELIMITER, $content) as $serialized) {
            if (empty($serialized)) {
                $this->logger->debug("found empty line in `$this->filePath` ... will break");
                break;
            }

            $this->logger->debug("found `$serialized` in `$this->filePath`");

            yield $this->deserialized($serialized);
        }
    }

    public function append(Event $event): EventStream
    {
        $this->flock(LOCK_EX);
        $serialized = $this->serialize($event);
        $newLine = "$serialized" . self::DELIMITER;
        fwrite($this->fileHandle, $newLine);
        fflush($this->fileHandle);
        $this->flock(LOCK_SH);

        $this->logger->debug("attached event as `$newLine` to `$this->filePath`");

        return $this;
    }

    public function __destruct()
    {
        $this->flock(LOCK_UN);
        fclose($this->fileHandle);
        $this->logger->debug("closed `$this->filePath`");
    }

    public function attachLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function flock(int $lockMethod): void
    {
        $result = flock($this->fileHandle, $lockMethod);
        if ($result === false) {
            throw new \RuntimeException("Could change lock to method: $lockMethod");
        }
    }

    public function serialize(Event $event): string
    {
        return serialize($event);
    }

    public function deserialized(string $serialized): Event
    {
        $obj = unserialize($serialized);

        if ($obj instanceof Event) {
            return $obj;
        }

        throw new \RuntimeException(
            "Expected object which implements Event but got " . get_class($obj)
        );
    }

    public function appendUnknown(EventStream $other): void
    {
        # TODO: make sure we only import unknown events
        foreach ($other as $event) {
            $this->append($event);
        }
    }
}