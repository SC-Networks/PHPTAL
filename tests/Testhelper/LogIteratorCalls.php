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

class LogIteratorCalls implements \Iterator
{
    /**
     * @var \Iterator
     */
    public $i;

    /**
     * @var string
     */
    public $log = '';

    public function __construct($arr)
    {
        if ($arr instanceof \Iterator) {
            $this->i = $arr;
        } else {
            $this->i = new \ArrayIterator($arr);
        }
    }

    public function current()
    {
        $this->log .= "current\n";
        return $this->i->current();
    }

    public function next()
    {
        $this->log .= "next\n";
        return $this->i->next();
    }

    public function key()
    {
        $this->log .= "key\n";
        return $this->i->key();
    }

    public function rewind()
    {
        $this->log .= "rewind\n";
        return $this->i->rewind();
    }

    public function valid()
    {
        $this->log .= "valid\n";
        return $this->i->valid();
    }
}
