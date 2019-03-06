<?php

namespace mad654\eventstore\FileEventStream;


use mad654\eventstore\Event;
use mad654\eventstore\EventStream\EventStream;
use mad654\eventstore\Logable;
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
 * TODO Refactor serialisation to json?
 */
final class FileEventStream implements EventStream, Logable
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
    private function __construct(string $rootDirPath, string $name)
    {
        $this->filePath = self::constructFilePath($rootDirPath, $name);
        $this->rootDirPath = $rootDirPath;
        $this->name = $name;
        $this->logger = new NullLogger();

        $this->flock(LOCK_SH);
        $this->logger->debug("opened `$this->filePath`");
    }

    public static function new(string $rooDirPath, string $id): self
    {
        if (file_exists(self::constructFilePath($rooDirPath, $id))) {
            throw new \RuntimeException(
                "Stream with id `$id` already exists"
            );
        }

        return new self($rooDirPath, $id);
    }

    public static function load(string $rooDirPath, string $id): self
    {
        if (!file_exists(self::constructFilePath($rooDirPath, $id))) {
            throw new \RuntimeException(
                "Stream with id `$id` not found"
            );
        }

        return new self($rooDirPath, $id);
    }

    private static function constructFilePath(string $rootDirPath, string $name): string
    {
        return $rootDirPath . DIRECTORY_SEPARATOR . $name . ".dat";
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
        $filesize = filesize($this->filePath);
        if ($filesize === 0) {
            return;
        }

        fseek($this->fileHandle(), 0);
        $content = fread($this->fileHandle(), $filesize);

        foreach (explode(self::DELIMITER, $content) as $serialized) {
            if (empty($serialized)) {
                $this->logger->debug("found empty line in `$this->filePath` ... will break");
                break;
            }

            $this->logger->debug("found `$serialized` in `$this->filePath`");

            yield $this->deserialize($serialized);
        }
    }

    public function append(Event $event): EventStream
    {
        $this->flock(LOCK_EX);
        $serialized = $this->serialize($event);
        $newLine = "$serialized" . self::DELIMITER;

        try {
            if (fwrite($this->fileHandle(), $newLine) === false) {
                throw new \RuntimeException("write failed");
            }
            if (fflush($this->fileHandle()) === false) {
                throw new \RuntimeException("flush failed");
            }
            clearstatcache(true, $this->filePath);
        } catch (\Throwable $e) {
            throw new \RuntimeException(sprintf(
                "Could not append event: `%s`",
                json_encode($event)),
                500,
                $e
            );
        } finally {
            $this->flock(LOCK_SH);
        }

        $this->logger->debug("attached event as `$newLine` to `$this->filePath`");

        return $this;
    }

    public function attachLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    private function flock(int $lockMethod): void
    {
        if (flock($this->fileHandle(), $lockMethod) === false) {
            throw new \RuntimeException("Could change lock to method: $lockMethod");
        }
    }

    public function serialize(Event $event): string
    {
        return serialize($event);
    }

    public function deserialize(string $serialized): Event
    {
        $obj = unserialize($serialized);

        if ($obj instanceof Event) {
            return $obj;
        }

        throw new \RuntimeException(
            "Expected object which implements Event but got " . get_class($obj)
        );
    }

    public function appendAll(EventStream $other): void
    {
        # TODO: write all at once
        foreach ($other as $event) {
            $this->append($event);
        }
    }

    private function fileHandle()
    {
        if (!is_resource($this->fileHandle)) {
            $fh = fopen($this->filePath, 'a+');

            if ($fh === false) {
                throw new \RuntimeException("Could not open storage file: `$this->filePath`");
            }

            $this->fileHandle = $fh;
        }

        return $this->fileHandle;
    }

    public function __sleep()
    {
        $this->unlockAndClose();
        return array_keys(get_object_vars($this));
    }

    public function __destruct()
    {
        $this->unlockAndClose();
    }

    private function unlockAndClose(): void
    {
        $this->flock(LOCK_UN);
        fclose($this->fileHandle());
        $this->logger->debug("closed `$this->filePath`");
    }


}