<?php
declare(strict_types=1);

namespace Tests\Testhelper;

use PhpTal\FilterInterface;

/**
 * Class MyPostFilter2
 * @package Testhelper
 */
class MyPostFilter2 implements FilterInterface
{
    /**
     * @param string $str
     * @return string
     */
    public function filter(string $str): string
    {
        return str_replace('test', 'test-filtered', $str);
    }
}
