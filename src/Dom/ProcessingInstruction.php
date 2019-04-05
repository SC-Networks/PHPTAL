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
 * processing instructions, including <?php blocks
 *
 * @package PHPTAL
 */
class ProcessingInstruction extends Node
{
    /**
     * use CodeWriter to compile this element to PHP code
     *
     * @param CodeWriter $codewriter
     */
    public function generateCode(CodeWriter $codewriter): void
    {
        if (preg_match('/^<\?(?:php|[=\s])/i', $this->getValueEscaped())) {
            // block will be executed as PHP
            $codewriter->pushHTML($this->getValueEscaped());
        } else {
            $codewriter->doEchoRaw("'<'");
            $codewriter->pushHTML(substr($codewriter->interpolateHTML($this->getValueEscaped()), 1));
        }
    }
}
