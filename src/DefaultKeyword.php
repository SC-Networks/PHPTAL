<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author Andrew Crites <explosion-pills@aysites.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal;

/**
 * Representation of the template 'default' keyword
 *
 * @package PHPTAL
 */
class DefaultKeyword implements \Countable
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return "''";
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return 1;
    }

    /**
     * @return \stdClass
     */
    public function jsonSerialize(): \stdClass
    {
        return new \stdClass();
    }
}
