<?php
/**
 * PHPTAL templating engine
 *
 * PHP Version 5
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @version  SVN: $Id: $
 * @link     http://phptal.org/
 */

namespace PhpTal\PreFilter;

class StripComments extends \PhpTal\PreFilter
{
    function filterDOM(\PhpTal\Dom\Element $element)
    {
        $defs = \PhpTal\Dom\Defs::getInstance();

        foreach ($element->childNodes as $node) {
            if ($node instanceof \PhpTal\Dom\Comment) {
                if ($defs->isCDATAElementInHTML($element->getNamespaceURI(), $element->getLocalName())) {
                    $textNode = new \PhpTal\Dom\CDATASection($node->getValueEscaped(), $node->getEncoding());
                    $node->parentNode->replaceChild($textNode, $node);
                } else {
                    $node->parentNode->removeChild($node);
                }
            } else if ($node instanceof \PhpTal\Dom\Element) {
                $this->filterDOM($node);
            }
        }
    }
}
