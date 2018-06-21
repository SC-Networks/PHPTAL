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
