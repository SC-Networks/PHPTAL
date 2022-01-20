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
     * @return Element|string (string in test only...)
     */
    abstract public function getResult();

    /**
     * @return void
     */
    abstract public function onDocumentStart(): void;

    /**
     * @return void
     */
    abstract public function onDocumentEnd(): void;

    /**
     * @param string $doctype
     *
     * @return void
     */
    abstract public function onDocType(string $doctype): void;

    /**
     * @param string $decl
     *
     * @return void
     */
    abstract public function onXmlDecl(string $decl): void;

    /**
     * @param string $data
     *
     * @return void
     */
    abstract public function onComment(string $data): void;

    /**
     * @param string $data
     *
     * @return void
     */
    abstract public function onCDATASection(string $data): void;

    /**
     * @param string $data
     *
     * @return void
     */
    abstract public function onProcessingInstruction(string $data): void;

    /**
     * @param string $element_qname
     * @param array<string, string> $attributes
     *
     * @return void
     */
    abstract public function onElementStart(string $element_qname, array $attributes): void;

    /**
     * @param string $data
     *
     * @return void
     */
    abstract public function onElementData(string $data): void;

    /**
     * @param string $qname
     *
     * @return void
     */
    abstract public function onElementClose(string $qname): void;

    /**
     * @param string $file
     * @param int $line
     *
     * @return void
     */
    public function setSource(string $file, int $line): void
    {
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @param string $encoding
     *
     * @return void
     */
    abstract public function setEncoding(string $encoding): void;
}
