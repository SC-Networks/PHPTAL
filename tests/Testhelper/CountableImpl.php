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

class CountableImpl implements \Countable
{
    /**
     * @var int
     */
    private $cnt;

    public function __construct(int $cnt = null)
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
