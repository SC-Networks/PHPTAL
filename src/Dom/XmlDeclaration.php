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
 * XML declaration node.
 *
 * @package PHPTAL
 */
class XmlDeclaration extends \PhpTal\Dom\Node
{
    public function generateCode(\PhpTal\Php\CodeWriter $codewriter)
    {
        $codewriter->setXmlDeclaration($this->getValueEscaped());
        $codewriter->doXmlDeclaration();
    }
}
