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
 * @see EventStorable
 * @package mad654\eventstore
 */
class FileEventStream implements EventStorable, Logable
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

        flock($this->fileHandle, LOCK_SH);
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

            yield unserialize($serialized);
        }
    }

    public function attach(Event $event): EventStorable
    {
        flock($this->fileHandle, LOCK_EX);
        $serialized = serialize($event);
        $newLine = "$serialized" . self::DELIMITER;
        fwrite($this->fileHandle, $newLine);
        flock($this->fileHandle, LOCK_SH);

        $this->logger->debug("attached event as `$newLine` to `$this->filePath`");

        return $this;
    }

    public function __destruct()
    {
        flock($this->fileHandle, LOCK_UN);
        fclose($this->fileHandle);
        $this->logger->debug("closed `$this->filePath`");
    }


    public function attachLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}