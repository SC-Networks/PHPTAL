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
 * @link     http://phptal.org/
 */

namespace PhpTal\PreFilter;

use PhpTal\Dom\Element;

class StripComments extends \PhpTal\PreFilter
{
    /**
     * Receives root PHPTAL DOM node of parsed file and should edit it in place.
     * Prefilters are called only once before template is compiled, so they can be slow.
     *
     * Default implementation does nothing. Override it.
     *
     * @see \PhpTal\Dom\Element class for methods and fields available.
     *
     * @param Element $element
     * @return void
     * @throws \PhpTal\Exception\PhpTalException
     */
    public function filterDOM(Element $element)
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
            } elseif ($node instanceof Element) {
                $this->filterDOM($node);
            }
        }
    }
}
