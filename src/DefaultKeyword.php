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

use Countable;
use stdClass;
use Stringable;

/**
 * Representation of the template 'default' keyword
 *
 * @package PHPTAL
 */
class DefaultKeyword implements Countable, Stringable
{
    public function __toString(): string
    {
        return "''";
    }
    public function count(): int
    {
        return 1;
    }
    public function jsonSerialize(): stdClass
    {
        return new stdClass();
    }
}
