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
use PhpTal\TalNamespace\Builtin;

/**
 * DOM Builder
 *
 * @package PHPTAL
 */
class PHPTALDocumentBuilder extends DocumentBuilder
{
    /**
     * @var XmlnsState
     */
    private $xmlns;

    /**
     * @var string
     */
    private $encoding;

    private ?Element $documentElement = null;

    /**
     * PHPTALDocumentBuilder constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->xmlns = new XmlnsState([], '');
    }

    public function getResult(): Element
    {
        return $this->documentElement;
    }

    /**
     * @return XmlnsState
     */
    protected function getXmlnsState(): XmlnsState
    {
        return $this->xmlns;
    }

    // ~~~~~ XmlParser implementation ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    public function onDocumentStart(): void
    {
        $this->documentElement = new Element('documentElement', Builtin::NS_TAL, [], $this->getXmlnsState());
        $this->documentElement->setSource($this->file, $this->line);
        $this->current = $this->documentElement;
    }

    public function onDocumentEnd(): void
    {
        if (count($this->stack) > 0) {
            $left = '</' . $this->current->getQualifiedName() . '>';
            for ($i = count($this->stack) - 1; $i > 0; $i--) {
                $left .= '</' . $this->stack[$i]->getQualifiedName() . '>';
            }
            throw new ParserException(
                'Not all elements were closed before end of the document. Missing: ' . $left,
                $this->file,
                $this->line
            );
        }
    }

    /**
     * @param string $doctype
     *
     * @return void
     * @throws PhpTalException
     */
    public function onDocType(string $doctype): void
    {
        $this->pushNode(new DocumentType($doctype, $this->encoding));
    }

    /**
     * @param string $decl
     *
     * @return void
     * @throws PhpTalException
     */
    public function onXmlDecl(string $decl): void
    {
        if (!$this->encoding) {
            throw new PhpTalException('Encoding not set');
        }
        $this->pushNode(new XmlDeclaration($decl, $this->encoding));
    }

    /**
     * @param string $data
     * @return void
     * @throws PhpTalException
     */
    public function onComment(string $data): void
    {
        $this->pushNode(new Comment($data, $this->encoding));
    }

    /**
     * @param string $data
     *
     * @return void
     * @throws PhpTalException
     */
    public function onCDATASection(string $data): void
    {
        $this->pushNode(new CDATASection($data, $this->encoding));
    }

    /**
     * @param string $data
     *
     * @return void
     * @throws PhpTalException
     */
    public function onProcessingInstruction(string $data): void
    {
        $this->pushNode(new ProcessingInstruction($data, $this->encoding));
    }

    /**
     * @param string $element_qname
     * @param array<string, string> $attributes
     *
     * @throws ParserException
     * @throws PhpTalException
     * @throws TemplateException
     */
    public function onElementStart(string $element_qname, array $attributes): void
    {
        $this->xmlns = $this->xmlns->newElement($attributes);

        if (preg_match('/^([^:]+):/', $element_qname, $m)) {
            $prefix = $m[1];
            $namespace_uri = $this->xmlns->prefixToNamespaceURI($prefix);
            if ($namespace_uri === null) {
                throw new ParserException(
                    "There is no namespace declared for prefix of element < $element_qname >. You must have xmlns:$prefix declaration in the same document.",
                    $this->file,
                    $this->line
                );
            }
        } else {
            $namespace_uri = $this->xmlns->getCurrentDefaultNamespaceURI();
        }

        $attrnodes = [];
        foreach ($attributes as $qname => $value) {
            if (preg_match('/^([^:]+):(.+)$/', $qname, $m)) {
                [, $prefix, $local_name] = $m;
                $attr_namespace_uri = $this->xmlns->prefixToNamespaceURI($prefix);

                if ($attr_namespace_uri === null) {
                    throw new ParserException(
                        "There is no namespace declared for prefix of attribute $qname of element < $element_qname >. You must have xmlns:$prefix declaration in the same document.",
                        $this->file,
                        $this->line
                    );
                }
            } else {
                $local_name = $qname;
                $attr_namespace_uri = ''; // default NS. Attributes don't inherit namespace per XMLNS spec
            }

            if ($this->xmlns->isHandledNamespace($attr_namespace_uri)
                && !$this->xmlns->isValidAttributeNS($attr_namespace_uri, $local_name)) {
                throw new ParserException(
                    "Attribute '$qname' is in '$attr_namespace_uri' namespace, but is not a supported PHPTAL attribute",
                    $this->file,
                    $this->line
                );
            }

            $attrnodes[] = new Attr($qname, $attr_namespace_uri, $value, $this->encoding);
        }

        $node = new Element($element_qname, $namespace_uri, $attrnodes, $this->getXmlnsState());
        $this->pushNode($node);
        $this->stack[] = $this->current;
        $this->current = $node;
    }

    /**
     * @param string $data
     *
     * @return void
     * @throws PhpTalException
     */
    public function onElementData(string $data): void
    {
        $this->pushNode(new Text($data, $this->encoding));
    }

    /**
     * @param string $qname
     *
     * @return void
     * @throws ParserException
     */
    public function onElementClose(string $qname): void
    {
        if ($this->current === $this->documentElement) {
            throw new ParserException(
                "Found closing tag for < $qname > where there are no open tags",
                $this->file,
                $this->line
            );
        }
        if ($this->current->getQualifiedName() !== $qname) {
            throw new ParserException(
                'Tag closure mismatch, expected < /' . $this->current->getQualifiedName() .
                ' > (opened in line ' . $this->current->getSourceLine() . ') but found < /' . $qname . ' >',
                $this->file,
                $this->line
            );
        }
        $this->current = array_pop($this->stack);
        if ($this->current instanceof Element) {
            $this->xmlns = $this->current->getXmlnsState(); // restore namespace prefixes info to previous state
        }
    }

    /**
     * @param Node $node
     *
     * @return void
     * @throws PhpTalException
     */
    private function pushNode(Node $node): void
    {
        $node->setSource($this->file, $this->line);
        $this->current->appendChild($node);
    }

    /**
     * @param string $encoding
     *
     * @return void
     */
    public function setEncoding(string $encoding): void
    {
        $this->encoding = $encoding;
    }
}
