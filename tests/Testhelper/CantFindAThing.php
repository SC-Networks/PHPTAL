<?php

namespace Tests\Testhelper;

use PhpTal\SourceInterface;
use PhpTal\SourceResolverInterface;

/**
 * Class CantFindAThing
 * @package Testhelper
 */
class CantFindAThing implements SourceResolverInterface
{
    /**
     * @param string $path
     * @return \PhpTal\SourceInterface
     */
    public function resolve(string $path): ?SourceInterface
    {
        return null;
    }
}
