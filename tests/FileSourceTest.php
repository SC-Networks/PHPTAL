<?php

declare(strict_types=1);

namespace Tests;

use PhpTal\Exception\IOException;
use PhpTal\FileSource;
use PHPUnit\Framework\TestCase;

class FileSourceTest extends TestCase
{
    public function testInstantiationThrowsErrorIfPathDoesNotExist(): void
    {
        $path = __DIR__ . '/does/not/exist';

        $this->expectException(IOException::class);
        $this->expectExceptionMessage(
            sprintf('Unable to find real path of file \'%s\' (in %s)', $path, getcwd())
        );

        new FileSource($path);
    }

    public function testInstantiationThrowsErrorIfDirectory(): void
    {
        $dir = __DIR__;

        $this->expectException(IOException::class);
        $this->expectExceptionMessage(
            sprintf('Path \'%s\' points to a directory', $dir)
        );

        new FileSource($dir);
    }
}
