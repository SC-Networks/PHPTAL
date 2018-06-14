<?php
declare(strict_types=1);

namespace Tests\Testhelper;

use PhpTal\FilterInterface;

/**
 * Class MyPostFilter
 * @package Testhelper
 */
class MyPostFilter implements FilterInterface
{
    /**
     * @param string $str
     *
     * @return string
     */
    public function filter(string $str): string
    {
        if (preg_match('|<root>(.*?)</root>|s', $str, $m)) {
            return $m[1];
        }
        return $str;
    }
}
