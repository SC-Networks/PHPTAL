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
 * DOM Builder
 *
 * @package PHPTAL
 */
abstract class DocumentBuilder
{
    /**
     * @var Node[]
     */
    protected $stack;

    /**
     * @var Element
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
     * @return mixed
     */
    abstract public function getResult();

    /**
     * @return mixed
     */
    abstract public function onDocumentStart();

    /**
     * @return mixed
     */
    abstract public function onDocumentEnd();

    /**
     * @param $doctype
     * @return mixed
     */
    abstract public function onDocType($doctype);

    /**
     * @param $decl
     * @return mixed
     */
    abstract public function onXmlDecl($decl);

    /**
     * @param $data
     * @return mixed
     */
    abstract public function onComment($data);

    /**
     * @param $data
     * @return mixed
     */
    abstract public function onCDATASection($data);

    /**
     * @param $data
     * @return mixed
     */
    abstract public function onProcessingInstruction($data);

    /**
     * @param $element_qname
     * @param array $attributes
     * @return mixed
     */
    abstract public function onElementStart($element_qname, array $attributes);

    /**
     * @param $data
     * @return mixed
     */
    abstract public function onElementData($data);

    /**
     * @param $qname
     * @return mixed
     */
    abstract public function onElementClose($qname);

    /**
     * @param string $file
     * @param int $line
     *
     * @return void
     */
    public function setSource($file, $line)
    {
        $this->file = $file;
        $this->line = $line;
    }

    /**
     * @param string $encoding
     * @return mixed
     */
    abstract public function setEncoding($encoding);
}
