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

namespace PhpTal\Dom;

/**
 * processing instructions, including <?php blocks
 *
 * @package PHPTAL
 */
class ProcessingInstruction extends \PhpTal\Dom\Node
{
    public function generateCode(\PhpTal\Php\CodeWriter $codewriter)
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
