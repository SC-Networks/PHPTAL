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

use PhpTal\Php\CodeWriter;
use Stringable;

/**
 * Document node abstract class.
 *
 * @package PHPTAL
 */
abstract class Node implements Stringable
{
    /**
     * @var Element|null
     */
    public $parentNode;

    private ?string $source_file = null;

    private ?int $source_line = null;

    /**
     * Node constructor.
     */
    public function __construct(private string $value_escaped, private readonly string $encoding)
    {
    }

    /**
     * hint where this node is in source code
     */
    public function setSource(string $file, int $line): void
    {
        $this->source_file = $file;
        $this->source_line = $line;
    }

    /**
     * file from which this node comes from
     */
    public function getSourceFile(): string
    {
        return $this->source_file;
    }

    /**
     * line on which this node was defined
     */
    public function getSourceLine(): int
    {
        return $this->source_line;
    }

    /**
     * depends on node type. Value will be escaped according to context that node comes from.
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
     */
    public function getValue(): string
    {
        return html_entity_decode($this->getValueEscaped(), ENT_QUOTES, $this->encoding);
    }

    /**
     * encoding used by value of this node.
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * use CodeWriter to compile this element to PHP code
     */
    abstract public function generateCode(CodeWriter $codewriter): void;

    public function __toString(): string
    {
        return ' “' . $this->getValue() . '” ';
    }
}
