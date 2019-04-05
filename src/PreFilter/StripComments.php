<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal\PreFilter;

use PhpTal\Dom\CDATASection;
use PhpTal\Dom\Comment;
use PhpTal\Dom\Defs;
use PhpTal\Dom\Element;
use PhpTal\PreFilter;

class StripComments extends PreFilter
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
    public function filterDOM(Element $element): void
    {
        $defs = Defs::getInstance();

        foreach ($element->childNodes as $node) {
            if ($node instanceof Comment) {
                if ($defs->isCDATAElementInHTML($element->getNamespaceURI(), $element->getLocalName())) {
                    $textNode = new CDATASection($node->getValueEscaped(), $node->getEncoding());
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
