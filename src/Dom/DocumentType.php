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
 * @link     http://phptal.org/
 */

namespace PhpTal\Dom;

/**
 * Document doctype representation.
 *
 * @package PHPTAL
 */
class DocumentType extends \PhpTal\Dom\Node
{
    public function generateCode(\PhpTal\Php\CodeWriter $codewriter)
    {
        if ($codewriter->getOutputMode() === \PhpTal\PHPTAL::HTML5) {
            $codewriter->setDocType('<!DOCTYPE html>');
        } else {
            $codewriter->setDocType($this->getValueEscaped());
        }
        $codewriter->doDoctype();
    }
}
