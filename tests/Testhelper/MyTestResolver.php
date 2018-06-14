<?php

namespace Tests\Testhelper;

use PhpTal\SourceInterface;
use PhpTal\SourceResolverInterface;
use PhpTal\StringSource;

/**
 * Class MyTestResolver
 * @package Testhelper
 */
class MyTestResolver implements SourceResolverInterface
{
    /**
     * @var int
     */
    public $called = 0;

    /**
     * @param string $path
     * @return SourceInterface
     */
    public function resolve(string $path): ?SourceInterface
    {
        $this->called++;
        return new StringSource("<p>found $path</p>");
    }
}
