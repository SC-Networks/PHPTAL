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
 * @version  SVN: $Id$
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
    public function __toString()
    {
        return "''";
    }

    public function count()
    {
        return 1;
    }

    public function jsonSerialize()
    {
        return new \stdClass;
    }
}
