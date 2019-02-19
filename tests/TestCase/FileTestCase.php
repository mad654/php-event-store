<?php

namespace mad654\eventstore\TestCase;


use PHPUnit\Framework\TestCase;

abstract class FileTestCase extends TestCase
{
    private $rootDirPath;

    protected function setUp()
    {
        parent::setUp();

        $this->rootDirPath = $this->tmpDir();
    }

    private function tmpDir(): string
    {
        $tempfile = tempnam(sys_get_temp_dir(), '');
        if (file_exists($tempfile)) {
            unlink($tempfile);
        }

        if (!mkdir($tempfile) && !is_dir($tempfile)) {
            throw new \RuntimeException(
                "Could not create tmp dir: $tempfile"
            );
        }

        return $tempfile;
    }

    protected function tearDown()
    {
        $this->deleteRecursive($this->rootDirPath);
        parent::tearDown();
    }

    private function deleteRecursive(string $dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (is_dir($dir . "/" . $object))
                        $this->deleteRecursive($dir . "/" . $object);
                    else
                        unlink($dir . "/" . $object);
                }
            }
            rmdir($dir);
        }
    }

    protected function rootDirPath(): string
    {
        return $this->rootDirPath;
    }
}