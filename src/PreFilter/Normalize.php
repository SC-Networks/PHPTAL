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

use PhpTal\Dom\Attr;
use PhpTal\Dom\Element;
use PhpTal\Dom\Text;
use PhpTal\PreFilter;
use PhpTal\TalNamespace\Builtin;

/**
 * Collapses conscutive whitespace, trims attributes, merges adjacent text nodes
 */
class Normalize extends PreFilter
{
    /**
     * Receives template source code and is expected to return new source.
     * Prefilters are called only once before template is compiled, so they can be slow.
     *
     * Default implementation does nothing. Override it.
     *
     * @param string $src markup to filter
     *
     * @return string
     */
    public function filter(string $src): string
    {
        return str_replace("\r\n", "\n", $src);
    }

    /**
     * Receives root PHPTAL DOM node of parsed file and should edit it in place.
     * Prefilters are called only once before template is compiled, so they can be slow.
     *
     * Default implementation does nothing. Override it.
     *
     * @see \PhpTal\Dom\Element class for methods and fields available.
     *
     * @param Element $root PHPTAL DOM node to modify in place
     *
     * @return void
     * @throws \PhpTal\Exception\PhpTalException
     */
    public function filterDOM(Element $root): void
    {
        // let xml:space=preserve preserve attributes as well
        if ($root->getAttributeNS(Builtin::NS_XML, 'space') === 'preserve') {
            $this->findElementToFilter($root);
            return;
        }

        $this->normalizeAttributes($root);

        // <pre> may have attributes normalized
        if ($this->isSpaceSensitiveInXHTML($root)) {
            $this->findElementToFilter($root);
            return;
        }

        /** @var Text $lastTextNode */
        $lastTextNode = null;
        foreach ($root->childNodes as $node) {
            // CDATA is not normalized by design
            if ($node instanceof Text) {
                $norm = $this->normalizeSpace($node->getValueEscaped(), $node->getEncoding());
                $node->setValueEscaped($norm);

                if ($norm === '') {
                    $root->removeChild($node);
                } elseif ($lastTextNode) {
                    // "foo " . " bar" gives 2 spaces.
                    $norm = $lastTextNode->getValueEscaped() . ltrim($norm, ' ');

                    $lastTextNode->setValueEscaped($norm); // assumes all nodes use same encoding (they do)
                    $root->removeChild($node);
                } else {
                    $lastTextNode = $node;
                }
            } else {
                $lastTextNode = null;
                if ($node instanceof Element) {
                    $this->filterDOM($node);
                }
            }
        }
    }

    /**
     * @param Element $element
     *
     * @return bool
     */
    protected function isSpaceSensitiveInXHTML(Element $element): bool
    {
        $ln = $element->getLocalName();
        $namespaceURI = $element->getNamespaceURI();
        return in_array($ln, ['script', 'pre', 'textarea'], true)
            && ($namespaceURI === Builtin::NS_XHTML || $namespaceURI === '');
    }

    /**
     * @param Element $root
     *
     * @return void
     * @throws \PhpTal\Exception\PhpTalException
     */
    protected function findElementToFilter(Element $root): void
    {
        foreach ($root->childNodes as $node) {
            if (!$node instanceof Element) {
                continue;
            }

            if ($node->getAttributeNS(Builtin::NS_XML, 'space') === 'default') {
                $this->filterDOM($node);
            }
        }
    }

    /**
     * does not trim
     *
     * @param string $text
     * @param string $encoding
     *
     * @return string
     */
    protected function normalizeSpace(string $text, string $encoding): string
    {
        $utf_regex_mod = $encoding === 'UTF-8' ? 'u' : '';

        return preg_replace('/[ \t\r\n]+/' . $utf_regex_mod, ' ', $text); // \s removes nbsp
    }

    /**
     * @param Element $element
     *
     * @return void
     */
    protected function normalizeAttributes(Element $element): void
    {
        foreach ($element->getAttributeNodes() as $attrnode) {
            // skip replaced attributes (because getValueEscaped on them is meaningless)
            if ($attrnode->getReplacedState() !== Attr::NOT_REPLACED) {
                continue;
            }

            $val = $this->normalizeSpace($attrnode->getValueEscaped(), $attrnode->getEncoding());
            $attrnode->setValueEscaped(trim($val, ' '));
        }
    }
}
