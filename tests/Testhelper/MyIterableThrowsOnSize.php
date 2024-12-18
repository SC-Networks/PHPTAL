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

use Countable;

class MyIterableThrowsOnSize extends MyIterable implements Countable
{
    /**
     * @throws SizeCalledException
     */
    public function count(): int
    {
        throw new SizeCalledException('count() called');
    }

    /**
     * @throws SizeCalledException
     */
    public function length(): never
    {
        throw new SizeCalledException('length() called');
    }

    /**
     * @throws SizeCalledException
     */
    public function size(): never
    {
        throw new SizeCalledException('size() called');
    }
}
