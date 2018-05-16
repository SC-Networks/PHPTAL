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
 * DOM Builder
 *
 * @package PHPTAL
 */
abstract class DocumentBuilder
{
    protected $_stack;   /* array<\PhpTal\Dom\Node> */
    protected $_current; /* \PhpTal\Dom\Node */

    protected $file, $line;

    public function __construct()
    {
        $this->_stack = array();
    }

    abstract public function getResult();

    abstract public function onDocumentStart();

    abstract public function onDocumentEnd();

    abstract public function onDocType($doctype);

    abstract public function onXmlDecl($decl);

    abstract public function onComment($data);

    abstract public function onCDATASection($data);

    abstract public function onProcessingInstruction($data);

    abstract public function onElementStart($element_qname, array $attributes);

    abstract public function onElementData($data);

    abstract public function onElementClose($qname);

    public function setSource($file, $line)
    {
        $this->file = $file; $this->line = $line;
    }

    abstract public function setEncoding($encoding);
}

