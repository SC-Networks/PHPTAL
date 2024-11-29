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

use Stringable;

/**
 * Representation of the template 'nothing' keyword
 *
 * @package PHPTAL
 */
class NothingKeyword implements KeywordsInterface, Stringable
{
    public function __toString(): string
    {
        return 'null';
    }
    public function count(): int
    {
        return 0;
    }
    public function jsonSerialize(): ?string
    {
        return null;
    }
}
