<?php
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
    public function __toString()
    {
        return 'null';
    }

    public function count()
    {
        return 0;
    }

    public function jsonSerialize()
    {
        return null;
    }
}
