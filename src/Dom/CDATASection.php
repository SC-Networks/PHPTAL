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
use PhpTal\PHPTAL;

/**
 * Outputs <![CDATA[ ]]> blocks, sometimes converts them to text
 * @todo this might be moved to CDATA processing in Element
 *
 * @package PHPTAL
 */
class CDATASection extends Node
{
    /**
     * use CodeWriter to compile this element to PHP code
     *
     * @param CodeWriter $codewriter
     */
    public function generateCode(CodeWriter $codewriter): void
    {
        $mode = $codewriter->getOutputMode();
        $value = $this->getValueEscaped();
        $inCDATAelement = Defs::getInstance()->isCDATAElementInHTML(
            $this->parentNode->getNamespaceURI(),
            $this->parentNode->getLocalName()
        );

        // in HTML5 must limit it to <script> and <style>
        if ($mode === PHPTAL::HTML5 && $inCDATAelement) {
            $codewriter->pushHTML($codewriter->interpolateCDATA(str_replace('</', '<\/', $value)));
        } elseif (($mode === PHPTAL::XHTML && $inCDATAelement)  // safe for text/html
            || ($mode === PHPTAL::XML && preg_match('/[<>&]/', $value))  // non-useless in XML
            || ($mode !== PHPTAL::HTML5 && preg_match('/<\?|<_|\${structure/', $value))) {
            // hacks with structure (in X[HT]ML) may need it
            // in text/html "</" is dangerous and the only sensible way to escape is ECMAScript string escapes.
            if ($mode === PHPTAL::XHTML) {
                $value = str_replace('</', '<\/', $value);
            }

            $codewriter->pushHTML($codewriter->interpolateCDATA('<![CDATA[' . $value . ']]>'));
        } else {
            $codewriter->pushHTML($codewriter->interpolateHTML(
                htmlspecialchars($value, ENT_QUOTES, $codewriter->getEncoding())
            ));
        }
    }
}
