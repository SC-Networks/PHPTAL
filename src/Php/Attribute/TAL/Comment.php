<?php
/**
 * PHPTAL templating engine
 *
 * PHP Version 5
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version  SVN: $Id$
 * @link     http://phptal.org/
 */

namespace PhpTal\Php\Attribute\TAL;

/**
 * @package PHPTAL
 */
class Comment extends \PhpTal\Php\Attribute
{
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $codewriter->doComment($this->expression);
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
    }
}
