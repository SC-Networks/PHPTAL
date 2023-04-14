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

/**
 * DOM Builder
 *
 * @package PHPTAL
 */
abstract class DocumentBuilder
{
    /**
     * @var array<Node&Element>
     */
    protected $stack;

    /**
     * @var (Element&Node)|null
     */
    protected $current;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var int
     */
    protected $line;

    /**
     * DocumentBuilder constructor.
     */
    public function __construct()
    {
        $this->stack = [];
    }

    /**
     * @return Element
     */
    abstract public function getResult();

    abstract public function onDocumentStart(): void;

    abstract public function onDocumentEnd(): void;

    abstract public function onDocType(string $doctype): void;

    abstract public function onXmlDecl(string $decl): void;

    abstract public function onComment(string $data): void;

    abstract public function onCDATASection(string $data): void;

    abstract public function onProcessingInstruction(string $data): void;

    /**
     * @param array<string, string> $attributes
     *
     */
    abstract public function onElementStart(string $element_qname, array $attributes): void;

    abstract public function onElementData(string $data): void;

    abstract public function onElementClose(string $qname): void;

    public function setSource(string $file, int $line): void
    {
        $this->file = $file;
        $this->line = $line;
    }

    abstract public function setEncoding(string $encoding): void;
}
