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

namespace PhpTal\Dom;

use PhpTal\Php\CodeWriter;

/**
 * @package PHPTAL
 */
class Comment extends Node
{
    /**
     * use CodeWriter to compile this element to PHP code
     */
    public function generateCode(CodeWriter $codewriter): void
    {
        if (!preg_match('/^\s*!/', $this->getValueEscaped())) {
            $codewriter->pushHTML('<!--'.$this->getValueEscaped().'-->');
        }
    }
}
