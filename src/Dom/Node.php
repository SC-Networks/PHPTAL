<?php
declare(strict_types=1);


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

use PhpTal\Php\CodeWriter;

/**
 * Document node abstract class.
 *
 * @package PHPTAL
 */
abstract class Node
{
    /**
     * @var Element
     */
    public $parentNode;

    /**
     * @var string
     */
    private $value_escaped;

    /**
     * @var string
     */
    private $source_file;

    /**
     * @var int
     */
    private $source_line;

    /**
     * @var string
     */
    private $encoding;

    /**
     * Node constructor.
     *
     * @param string $value_escaped
     * @param string $encoding
     */
    public function __construct(string $value_escaped, string $encoding)
    {
        $this->value_escaped = $value_escaped;
        $this->encoding = $encoding;
    }

    /**
     * hint where this node is in source code
     *
     * @param string $file
     * @param int $line
     */
    public function setSource(string $file, int $line): void
    {
        $this->source_file = $file;
        $this->source_line = $line;
    }

    /**
     * file from which this node comes from
     *
     * @return string
     */
    public function getSourceFile(): string
    {
        return $this->source_file;
    }

    /**
     * line on which this node was defined
     *
     * @return int
     */
    public function getSourceLine(): int
    {
        return $this->source_line;
    }

    /**
     * depends on node type. Value will be escaped according to context that node comes from.
     *
     * @return string
     */
    public function getValueEscaped(): string
    {
        return preg_replace('/<\?(php|=)/mi', '<_', $this->value_escaped);
    }

    /**
     * Set value of the node (type-dependent) to this exact string.
     * String must be HTML-escaped and use node's encoding.
     *
     * @param string $value_escaped new content
     */
    public function setValueEscaped(string $value_escaped): void
    {
        $this->value_escaped = $value_escaped;
    }


    /**
     * get value as plain text. Depends on node type.
     *
     * @return string
     */
    public function getValue(): string
    {
        return html_entity_decode($this->getValueEscaped(), ENT_QUOTES, $this->encoding);
    }

    /**
     * encoding used by value of this node.
     *
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * use CodeWriter to compile this element to PHP code
     *
     * @param CodeWriter $codewriter
     */
    abstract public function generateCode(CodeWriter $codewriter): void;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return ' “' . $this->getValue() . '” ';
    }
}
