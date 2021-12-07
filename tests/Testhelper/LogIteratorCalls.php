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

use ArrayIterator;
use Iterator;

class LogIteratorCalls implements Iterator
{
    /**
     * @var Iterator
     */
    public $i;

    /**
     * @var string
     */
    public $log = '';

    public function __construct($arr)
    {
        if ($arr instanceof Iterator) {
            $this->i = $arr;
        } else {
            $this->i = new ArrayIterator($arr);
        }
    }

    public function current(): mixed
    {
        $this->log .= "current\n";
        return $this->i->current();
    }

    public function next(): void
    {
        $this->log .= "next\n";
        $this->i->next();
    }

    public function key(): mixed
    {
        $this->log .= "key\n";
        return $this->i->key();
    }

    public function rewind(): void
    {
        $this->log .= "rewind\n";
        $this->i->rewind();
    }

    public function valid(): bool
    {
        $this->log .= "valid\n";
        return $this->i->valid();
    }
}
