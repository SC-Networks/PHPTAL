<?php

namespace Tests\Testhelper;

use PhpTal\SourceInterface;
use PhpTal\SourceResolverInterface;

/**
 * Class MyCustomSourceResolver
 * @package Testhelper
 */
class MyCustomSourceResolver implements SourceResolverInterface
{
    /**
     * @param string $path
     *
     * @return SourceInterface
     */
    public function resolve(string $path): ?SourceInterface
    {
        return new MyCustomSource($path);
    }
}
