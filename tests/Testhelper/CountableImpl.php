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

class CountableImpl implements Countable
{
    private readonly int $cnt;

    public function __construct(null|int $cnt = null)
    {
        $this->cnt = $cnt ?? 0;
    }

    /**
     * @see Countable
     */
    public function count(): int
    {
        return $this->cnt;
    }
}
