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

use PhpTal\Exception\ParserException;
use PhpTal\Exception\PhpTalException;
use PhpTal\Exception\TemplateException;
use PhpTal\Php\CodeWriter;
use PhpTal\PHPTAL;
use PhpTal\TalNamespace\Builtin;
use PhpTal\TalNamespaceAttribute;
use PhpTal\TalNamespaceAttributeContent;
use PhpTal\TalNamespaceAttributeReplace;
use PhpTal\TalNamespaceAttributeSurround;

/**
 * Document Tag representation.
 *
 * @package PHPTAL
 */
class Element extends Node
{
    /**
     * @var string
     */
    protected $qualifiedName;

    /**
     * @var string
     */
    protected $namespace_uri;

    /**
     * @var Attr[]
     */
    private $attribute_nodes;

    /**
     * @var array
     */
    protected $replaceAttributes = [];

    /**
     * @var array
     */
    protected $contentAttributes = [];

    /**
     * @var array
     */
    protected $surroundAttributes = [];

    /**
     * @var bool
     */
    public $headFootDisabled = false;

    /**
     * @var string
     */
    public $headPrintCondition;

    /**
     * @var string
     */
    public $footPrintCondition;

    /**
     * @var bool
     */
    public $hidden = false;

    /**
     * W3C DOM interface
     * @var Node[]
     */
    public $childNodes = [];

    /**
     * @var XmlnsState
     */
    private $xmlns;

    /**
     * @param string $qname qualified name of the element, e.g. "tal:block"
     * @param string $namespace_uri namespace of this element
     * @param array $attribute_nodes array of \PhpTal\Dom\Attr elements
     * @param XmlnsState $xmlns object that represents namespaces/prefixes known in element's context
     *
     * @throws ParserException
     * @throws TemplateException
     */
    public function __construct(string $qname, string $namespace_uri, array $attribute_nodes, XmlnsState $xmlns)
    {
        $this->qualifiedName = $qname;
        $this->attribute_nodes = $attribute_nodes;
        $this->namespace_uri = $namespace_uri;
        $this->xmlns = $xmlns;

        // implements inheritance of element's namespace to tal attributes (<metal: use-macro>)
        foreach ($attribute_nodes as $index => $attr) {
            // it'll work only when qname == localname, which is good
            if ($this->xmlns->isValidAttributeNS($namespace_uri, $attr->getQualifiedName())) {
                $this->attribute_nodes[$index] = new Attr(
                    $attr->getQualifiedName(),
                    $namespace_uri,
                    $attr->getValueEscaped(),
                    $attr->getEncoding()
                );
            }
        }

        if ($this->xmlns->isHandledNamespace($this->namespace_uri)) {
            $this->headFootDisabled = true;
        }

        $talAttributes = $this->separateAttributes();
        $this->orderTalAttributes($talAttributes);
    }

    /**
     * returns object that represents namespaces known in element's context
     */
    public function getXmlnsState(): XmlnsState
    {
        return $this->xmlns;
    }

    /**
     * Replace <script> foo &gt; bar </script>
     * with <script>/*<![CDATA[* / foo > bar /*]]>* /</script>
     * This avoids gotcha in text/html.
     *
     * Note that \PhpTal\Dom\CDATASection::generate() does reverse operation, if needed!
     *
     * @return void
     * @throws PhpTalException
     */
    private function replaceTextWithCDATA(): void
    {
        $isCDATAelement = Defs::getInstance()->isCDATAElementInHTML($this->getNamespaceURI(), $this->getLocalName());

        if (!$isCDATAelement) {
            return;
        }

        $valueEscaped = ''; // sometimes parser generates split text nodes. "normalisation" is needed.
        $value = '';
        $encoding = '';
        foreach ($this->childNodes as $node) {
            // leave it alone if there is CDATA, comment, or anything else.
            if (!$node instanceof Text) {
                return;
            }

            $value .= $node->getValue();
            $valueEscaped .= $node->getValueEscaped();

            $encoding = $node->getEncoding(); // encoding of all nodes is the same
        }

        // only add cdata if there are entities
        // and there's no ${structure} (because it may rely on cdata syntax)
        if (strpos($valueEscaped, '&') === false || preg_match('/<\?|\${structure/', $value)) {
            return;
        }

        $this->childNodes = [];

        // appendChild sets parent
        $this->appendChild(new Text('/*', $encoding));
        $this->appendChild(new CDATASection('*/' . $value . '/*', $encoding));
        $this->appendChild(new Text('*/', $encoding));
    }

    /**
     * @param Node $child
     *
     * @return void
     * @throws PhpTalException
     */
    public function appendChild(Node $child): void
    {
        if ($child->parentNode) {
            $child->parentNode->removeChild($child);
        }
        $child->parentNode = $this;
        $this->childNodes[] = $child;
    }

    /**
     * @param Node $child
     *
     * @return void
     * @throws PhpTalException
     */
    public function removeChild(Node $child): void
    {
        foreach ($this->childNodes as $k => $node) {
            if ($child === $node) {
                $child->parentNode = null;
                array_splice($this->childNodes, $k, 1);
                return;
            }
        }
        throw new PhpTalException('Given node is not child of ' . $this->getQualifiedName());
    }

    /**
     * @param Node $newElement
     * @param Node $oldElement
     *
     * @return void
     * @throws PhpTalException
     */
    public function replaceChild(Node $newElement, Node $oldElement): void
    {
        foreach ($this->childNodes as $k => $node) {
            if ($node === $oldElement) {
                $oldElement->parentNode = null;

                if ($newElement->parentNode) {
                    $newElement->parentNode->removeChild($oldElement);
                }
                $newElement->parentNode = $this;

                $this->childNodes[$k] = $newElement;
                return;
            }
        }
        throw new PhpTalException('Given node is not child of ' . $this->getQualifiedName());
    }

    /**
     * use CodeWriter to compile this element to PHP code
     *
     * @param CodeWriter $codewriter
     *
     * @throws TemplateException
     * @throws PhpTalException
     */
    public function generateCode(CodeWriter $codewriter): void
    {
        try {
            /// self-modifications

            if ($codewriter->getOutputMode() === PHPTAL::XHTML) {
                $this->replaceTextWithCDATA();
            }

            /// code generation

            if ($this->getSourceLine()) {
                $codewriter->doComment('tag "' . $this->qualifiedName . '" from line ' . $this->getSourceLine());
            }

            $this->generateSurroundHead($codewriter);

            if (count($this->replaceAttributes) > 0) {
                foreach ($this->replaceAttributes as $att) {
                    $att->before($codewriter);
                    $att->after($codewriter);
                }
            } elseif (!$this->hidden) {
                // a surround tag may decide to hide us (tal:define for example)
                $this->generateHead($codewriter);
                $this->generateContent($codewriter);
                $this->generateFoot($codewriter);
            }

            $this->generateSurroundFoot($codewriter);
        } catch (TemplateException $e) {
            $e->hintSrcPosition($this->getSourceFile(), $this->getSourceLine());
            throw $e;
        }
    }

    /**
     * Array with \PhpTal\Dom\Attr objects
     *
     * @return Attr[]
     */
    public function getAttributeNodes(): array
    {
        return $this->attribute_nodes;
    }

    /**
     * Replace all attributes
     *
     * @param array $nodes array of \PhpTal\Dom\Attr objects
     */
    public function setAttributeNodes(array $nodes): void
    {
        $this->attribute_nodes = $nodes;
    }

    /** Returns true if the element contains specified PHPTAL attribute.
     *
     * @param string $qname
     *
     * @return bool
     */
    public function hasAttribute(string $qname): bool
    {
        foreach ($this->attribute_nodes as $attr) {
            if ($attr->getQualifiedName() === $qname) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string $ns_uri
     * @param string $localname
     *
     * @return bool
     */
    public function hasAttributeNS(string $ns_uri, string $localname): bool
    {
        return $this->getAttributeNodeNS($ns_uri, $localname) !== null;
    }

    /**
     * @param string $ns_uri
     * @param string $localname
     *
     * @return Attr
     */
    public function getAttributeNodeNS(string $ns_uri, string $localname): ?Attr
    {
        foreach ($this->attribute_nodes as $attr) {
            if ($attr->getNamespaceURI() === $ns_uri && $attr->getLocalName() === $localname) {
                return $attr;
            }
        }
        return null;
    }

    /**
     * @param string $ns_uri
     * @param string $localname
     *
     * @return void
     */
    public function removeAttributeNS(string $ns_uri, string $localname): void
    {
        foreach ($this->attribute_nodes as $k => $attr) {
            if ($attr->getNamespaceURI() === $ns_uri && $attr->getLocalName() === $localname) {
                unset($this->attribute_nodes[$k]);
                return;
            }
        }
    }

    /**
     * @param string $qname
     *
     * @return Attr
     */
    public function getAttributeNode(string $qname): ?Attr
    {
        foreach ($this->attribute_nodes as $attr) {
            if ($attr->getQualifiedName() === $qname) {
                return $attr;
            }
        }
        return null;
    }

    /**
     * If possible, use getAttributeNodeNS and setAttributeNS.
     *
     * NB: This method doesn't handle namespaces properly.
     *
     * @param $qname
     *
     * @return Attr
     */
    public function getOrCreateAttributeNode(string $qname): Attr
    {
        $attr = $this->getAttributeNode($qname);

        if ($attr !== null) {
            return $attr;
        }

        $attr = new Attr($qname, '', null, 'UTF-8'); // FIXME: should find namespace and encoding
        $this->attribute_nodes[] = $attr;
        return $attr;
    }

    /** Returns textual (unescaped) value of specified element attribute.
     *
     * @param string $namespace_uri
     * @param string $localname
     *
     * @return string
     */
    public function getAttributeNS(string $namespace_uri, string $localname): string
    {
        $n = $this->getAttributeNodeNS($namespace_uri, $localname);
        return $n !== null ? $n->getValue() : '';
    }

    /**
     * Set attribute value. Creates new attribute if it doesn't exist yet.
     *
     * @param string $namespace_uri full namespace URI. "" for default namespace
     * @param string $qname prefixed qualified name (e.g. "atom:feed") or local name (e.g. "p")
     * @param string $value unescaped value
     *
     * @return void
     */
    public function setAttributeNS(string $namespace_uri, string $qname, string $value): void
    {
        $localname = preg_replace('/^[^:]*:/', '', $qname);
        if (!($n = $this->getAttributeNodeNS($namespace_uri, $localname))) {
            $this->attribute_nodes[] = $n = new Attr($qname, $namespace_uri, null, 'UTF-8'); // FIXME: find encoding
        }
        $n->setValue($value);
    }

    /**
     * Returns true if this element or one of its PHPTAL attributes has some
     * content to print (an empty text node child does not count).
     *
     * @return bool
     */
    public function hasRealContent(): bool
    {
        if (count($this->contentAttributes) > 0) {
            return true;
        }

        foreach ($this->childNodes as $node) {
            if (!$node instanceof Text || $node->getValueEscaped() !== '') {
                return true;
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function hasRealAttributes(): bool
    {
        if ($this->hasAttributeNS(Builtin::NS_TAL, 'attributes')) {
            return true;
        }
        foreach ($this->attribute_nodes as $attr) {
            if ($attr->getReplacedState() !== Attr::HIDDEN) {
                return true;
            }
        }
        return false;
    }

    // ~~~~~ Generation methods may be called by some PHPTAL attributes ~~~~~

    /**
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    public function generateSurroundHead(CodeWriter $codewriter): void
    {
        foreach ($this->surroundAttributes as $att) {
            $att->before($codewriter);
        }
    }

    /**
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws PhpTalException
     */
    public function generateHead(CodeWriter $codewriter): void
    {
        if ($this->headFootDisabled) {
            return;
        }
        if ($this->headPrintCondition !== null) {
            $codewriter->doIf($this->headPrintCondition);
        }

        $html5mode = ($codewriter->getOutputMode() === PHPTAL::HTML5);

        if ($html5mode) {
            $codewriter->pushHTML('<' . $this->getLocalName());
        } else {
            $codewriter->pushHTML('<' . $this->qualifiedName);
        }

        $this->generateAttributes($codewriter);

        if (!$html5mode && $this->isEmptyNode($codewriter->getOutputMode())) {
            $codewriter->pushHTML('/>');
        } else {
            $codewriter->pushHTML('>');
        }

        if ($this->headPrintCondition !== null) {
            $codewriter->doEnd('if');
        }
    }

    /**
     * @param CodeWriter $codewriter
     * @param bool $realContent
     *
     * @return void
     */
    public function generateContent(CodeWriter $codewriter, bool $realContent = null): void
    {
        if (!$this->isEmptyNode($codewriter->getOutputMode())) {
            if ($realContent || count($this->contentAttributes) === 0) {
                foreach ($this->childNodes as $child) {
                    $child->generateCode($codewriter);
                }
            } else {
                foreach ($this->contentAttributes as $att) {
                    $att->before($codewriter);
                    $att->after($codewriter);
                }
            }
        }
    }

    /**
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws PhpTalException
     */
    public function generateFoot(CodeWriter $codewriter): void
    {
        if ($this->headFootDisabled) {
            return;
        }
        if ($this->isEmptyNode($codewriter->getOutputMode())) {
            return;
        }

        if ($this->footPrintCondition !== null) {
            $codewriter->doIf($this->footPrintCondition);
        }

        if ($codewriter->getOutputMode() === PHPTAL::HTML5) {
            $codewriter->pushHTML('</' . $this->getLocalName() . '>');
        } else {
            $codewriter->pushHTML('</' . $this->getQualifiedName() . '>');
        }

        if ($this->footPrintCondition !== null) {
            $codewriter->doEnd('if');
        }
    }

    /**
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    public function generateSurroundFoot(CodeWriter $codewriter): void
    {
        $v = count($this->surroundAttributes) - 1;
        for ($i = $v; $i >= 0; $i--) {
            $this->surroundAttributes[$i]->after($codewriter);
        }
    }

    /**
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    private function generateAttributes(CodeWriter $codewriter): void
    {
        $html5mode = ($codewriter->getOutputMode() === PHPTAL::HTML5);

        foreach ($this->getAttributeNodes() as $attr) {
            // xmlns:foo is not allowed in text/html
            if ($html5mode && $attr->isNamespaceDeclaration()) {
                continue;
            }

            switch ($attr->getReplacedState()) {
                case Attr::NOT_REPLACED:
                    $codewriter->pushHTML(' ' . $attr->getQualifiedName());
                    if ($codewriter->getOutputMode() !== PHPTAL::HTML5
                        || !Defs::getInstance()->isBooleanAttribute($attr->getQualifiedName())) {
                        $html = $codewriter->interpolateHTML($attr->getValueEscaped());
                        $codewriter->pushHTML('=' . $codewriter->quoteAttributeValue($html));
                    }
                    break;

                case Attr::HIDDEN:
                    break;

                case Attr::FULLY_REPLACED:
                    $codewriter->pushHTML($attr->getValueEscaped());
                    break;

                case Attr::VALUE_REPLACED:
                    $codewriter->pushHTML(' ' . $attr->getQualifiedName() . '="');
                    $codewriter->pushHTML($attr->getValueEscaped());
                    $codewriter->pushHTML('"');
                    break;
            }
        }
    }

    /**
     * @param int $mode
     *
     * @return bool
     */
    private function isEmptyNode(int $mode): bool
    {
        $modeXHTML = $mode === PHPTAL::XHTML || $mode === PHPTAL::HTML5;
        $isEmptyTagNS = Defs::getInstance()->isEmptyTagNS($this->getNamespaceURI(), $this->getLocalName());
        $modeXML = $mode === PHPTAL::XML && !$this->hasContent();

        return ($modeXHTML && $isEmptyTagNS) || $modeXML;
    }

    /**
     * @return bool
     */
    private function hasContent(): bool
    {
        return count($this->childNodes) > 0 || count($this->contentAttributes) > 0;
    }

    /**
     * @return array
     */
    private function separateAttributes(): array
    {
        $talAttributes = [];
        foreach ($this->attribute_nodes as $index => $attr) {
            // remove handled xml namespaces
            if (Defs::getInstance()->isHandledXmlNs($attr->getQualifiedName(), $attr->getValueEscaped())) {
                unset($this->attribute_nodes[$index]);
            } elseif ($this->xmlns->isHandledNamespace($attr->getNamespaceURI())) {
                $talAttributes[$attr->getQualifiedName()] = $attr;
                $attr->hide();
            } elseif (Defs::getInstance()->isBooleanAttribute($attr->getQualifiedName())) {
                $attr->setValue($attr->getLocalName());
            }
        }
        return $talAttributes;
    }

    /**
     * @param array $talAttributes
     *
     * @return void
     * @throws TemplateException
     * @throws ParserException
     */
    private function orderTalAttributes(array $talAttributes): void
    {
        $temp = [];
        /** @var Attr $domattr */
        foreach ($talAttributes as $key => $domattr) {
            $nsattr = Defs::getInstance()->getNamespaceAttribute($domattr->getNamespaceURI(), $domattr->getLocalName());
            if (array_key_exists($nsattr->getPriority(), $temp)) {
                throw new TemplateException(
                    sprintf(
                        "Attribute conflict in < %s > '%s' cannot appear with '%s'",
                        $this->qualifiedName,
                        $key,
                        $temp[$nsattr->getPriority()][0]->getNamespace()->getPrefix() . ':' .
                        $temp[$nsattr->getPriority()][0]->getLocalName()
                    ),
                    $this->getSourceFile(),
                    $this->getSourceLine()
                );
            }
            $temp[$nsattr->getPriority()] = [$nsattr, $domattr];
        }
        ksort($temp);

        foreach ($temp as $prio => [$nsattr, $domattr]) {
            /** @var TalNamespaceAttribute $nsattr */
            $handler = $nsattr->createAttributeHandler($this, $domattr->getValue());

            if ($nsattr instanceof TalNamespaceAttributeSurround) {
                $this->surroundAttributes[] = $handler;
            } elseif ($nsattr instanceof TalNamespaceAttributeReplace) {
                $this->replaceAttributes[] = $handler;
            } elseif ($nsattr instanceof TalNamespaceAttributeContent) {
                $this->contentAttributes[] = $handler;
            } else {
                throw new ParserException(
                    'Unknown namespace attribute class ' . get_class($nsattr),
                    $this->getSourceFile(),
                    $this->getSourceLine()
                );
            }
        }
    }

    /**
     * @return string
     */
    public function getQualifiedName(): string
    {
        return $this->qualifiedName;
    }

    /**
     * @return string
     */
    public function getNamespaceURI(): string
    {
        return $this->namespace_uri;
    }

    /**
     * @return string
     */
    public function getLocalName(): string
    {
        $n = explode(':', $this->qualifiedName, 2);
        return end($n);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return '<{' . $this->getNamespaceURI() . '}:' . $this->getLocalName() . '>';
    }

    /**
     * Set value of the node (type-dependent) to this exact string.
     * String must be HTML-escaped and use node's encoding.
     *
     * @param string $e new content
     * @throws PhpTalException
     */
    public function setValueEscaped(string $e): void
    {
        throw new PhpTalException('Not supported');
    }
}
