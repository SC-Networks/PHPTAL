<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal\Php\Attribute\TAL;

use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;

/**
 * @package PHPTAL
 */
class Comment extends Attribute
{
    /**
     * Called before element printing.
     *
     *
     */
    public function before(CodeWriter $codewriter): void
    {
        $codewriter->doComment($this->expression);
    }

    /**
     * Called after element printing.
     *
     *
     */
    public function after(CodeWriter $codewriter): void
    {
    }
}
