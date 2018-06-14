<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * PHP Version 5
 *
 * @category HTML
 * @package  PHPTAL
 * @author Andrew Crites <explosion-pills@aysites.com>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal;

/**
 * Representation of the template 'nothing' keyword
 *
 * @package PHPTAL
 */
class NothingKeyword implements KeywordsInterface
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'null';
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return 0;
    }

    /**
     * @return null
     */
    public function jsonSerialize(): ?string
    {
        return null;
    }
}
