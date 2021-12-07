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

use ArrayAccess;

class MyArray implements ArrayAccess
{

    /**
     * @var array
     */
    private $values = [];

    /**
     * @param $value
     *
     * @return void
     */
    public function push($value): void
    {
        $this->values[] = $value;
    }

    /**
     * @param string $index
     */
    public function offsetGet($index): mixed
    {
        return $this->values[$index];
    }

    /**
     * @param string $index
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($index, $value): void
    {
        $this->values[$index] = $value;
    }

    /**
     * @param mixed $of
     *
     * @return bool
     */
    public function offsetExists($of): bool
    {
        return isset($this->values[$of]);
    }

    /**
     * @param mixed $of
     *
     * @return void
     */
    public function offsetUnset($of): void
    {
        unset($this->values[$of]);
    }
}
