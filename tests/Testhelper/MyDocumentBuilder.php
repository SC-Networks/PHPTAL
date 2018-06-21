<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * Originally developed by Laurent Bedubourg and Kornel Lesiński
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @author   See contributors list @ github
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 * @link     https://github.com/SC-Networks/PHPTAL
 */

namespace Tests\Testhelper;

use PhpTal\Dom\DocumentBuilder;

/**
 * Class MyDocumentBuilder
 * @package Testhelper
 */
class MyDocumentBuilder extends DocumentBuilder
{

    /**
     * @var string
     */
    public $result;

    /**
     * @var int
     */
    public $elementStarts = 0;

    /**
     * @var int
     */
    public $elementCloses = 0;

    /**
     * @var int
     */
    public $specifics = 0;

    /**
     * @var int
     */
    public $datas = 0;

    /**
     * @var bool
     */
    public $allow_xmldec = true;

    public function __construct()
    {
        $this->result = '';
        parent::__construct();
    }

    /**
     * @param string $dt
     *
     * @return void
     */
    public function onDoctype(string $dt): void
    {
        $this->specifics++;
        $this->allow_xmldec = false;
        $this->result .= $dt;
    }

    /**
     * @param string $decl
     *
     * @return mixed
     * @throws \Exception
     */
    public function onXmlDecl(string $decl): void
    {
        if (!$this->allow_xmldec) {
            throw new \Exception('more than one xml decl');
        }
        $this->specifics++;
        $this->allow_xmldec = false;
        $this->result .= $decl;
    }

    /**
     * @param string $data
     *
     * @return void
     */
    public function onCDATASection(string $data): void
    {
        $this->onProcessingInstruction('<![CDATA[' . $data . ']]>');
    }

    /**
     * @param string $data
     * @return mixed
     */
    public function onProcessingInstruction(string $data): void
    {
        $this->specifics++;
        $this->allow_xmldec = false;
        $this->result .= $data;
    }

    public function onComment(string $data): void
    {
        $this->onProcessingInstruction('<!--' . $data . '-->');
    }

    public function onElementStart(string $element_qname, array $attributes): void
    {
        $this->allow_xmldec = false;
        $this->elementStarts++;
        $this->result .= "<$element_qname";
        $pairs = array();
        foreach ($attributes as $key => $value) {
            $pairs[] = "$key=\"$value\"";
        }
        if (count($pairs) > 0) {
            $this->result .= ' ' . join(' ', $pairs);
        }
        $this->result .= '>';
    }

    public function onElementClose(string $qname): void
    {
        $this->allow_xmldec = false;
        $this->elementCloses++;
        $this->result .= "</$qname>";
    }

    public function onElementData(string $data): void
    {
        $this->datas++;
        $this->result .= $data;
    }

    public function onDocumentStart(): void
    {
    }

    public function onDocumentEnd(): void
    {
    }

    public function getResult()
    {
        return $this->result;
    }

    public function setEncoding(string $encoding): void
    {
    }
}

