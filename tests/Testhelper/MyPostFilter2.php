<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * Originally developed by Laurent Bedubourg and Kornel Lesiński
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @author   See contributors list @ github
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 * @link     https://github.com/SC-Networks/PHPTAL
 */

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
