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

use Iterator;

class MyIterable implements Iterator
{
    private int $index;

    /**
     * @var int
     */
    protected $size;

    public function __construct(int $size)
    {
        $this->index = 0;
        $this->size = $size;
    }

    public function rewind(): void
    {
        $this->index = 0;
    }

    public function current(): int
    {
        return $this->index;
    }

    public function key(): int
    {
        return $this->index;
    }

    public function next(): void
    {
        $this->index++;
    }

    public function valid(): bool
    {
        return $this->index < $this->size;
    }
}
