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
 * node that represents element's attribute
 *
 * @package PHPTAL
 */
class Attr
{
    final public const HIDDEN = -1;
    final public const NOT_REPLACED = 0;
    final public const VALUE_REPLACED = 1;
    final public const FULLY_REPLACED = 2;

    /**
     * attribute's value can be overriden with a variable
     * @var string|null
     */
    private $phpVariable;

    /**
     * @var int
     */
    private $replacedState = 0;

    /**
     * @param string $qualified_name attribute name with prefix
     * @param string $namespace_uri full namespace URI or empty string
     * @param string $value_escaped value with HTML-escaping
     * @param string $encoding character encoding used by the value
     */
    public function __construct(private string $qualified_name, private string $namespace_uri, private ?string $value_escaped, private string $encoding)
    {
    }

    /**
     * get character encoding used by this attribute.
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * get full namespace URI. "" for default namespace.
     */
    public function getNamespaceURI(): string
    {
        return $this->namespace_uri;
    }

    /**
     * get attribute name including namespace prefix, if any
     */
    public function getQualifiedName(): string
    {
        return $this->qualified_name;
    }

    /**
     * get "foo" of "ns:foo" attribute name
     */
    public function getLocalName(): string
    {
        $n = explode(':', $this->qualified_name, 2);
        return end($n);
    }

    /**
     * Returns true if this attribute is ns declaration (xmlns="...")
     */
    public function isNamespaceDeclaration(): bool
    {
        return (bool) preg_match('/^xmlns(?:$|:)/', $this->qualified_name);
    }


    /**
     * get value as plain text
     */
    public function getValue(): string
    {
        return html_entity_decode($this->value_escaped, ENT_QUOTES, $this->encoding);
    }

    /**
     * set plain text as value
     */
    public function setValue(string $val): void
    {
        $this->value_escaped = htmlspecialchars($val, ENT_QUOTES, $this->encoding);
    }

    /**
     * Depends on replaced state.
     * If value is not replaced, it will return it with HTML escapes.
     *
     * @see getReplacedState()
     * @see overwriteValueWithVariable()
     */
    public function getValueEscaped(): ?string
    {
        return $this->value_escaped;
    }

    /**
     * Set value of the attribute to this exact string.
     * String must be HTML-escaped and use attribute's encoding.
     *
     * @param string $value_escaped new content
     */
    public function setValueEscaped($value_escaped): void
    {
        $this->replacedState = self::NOT_REPLACED;
        $this->value_escaped = $value_escaped;
    }

    /**
     * set PHP code as value of this attribute. Code is expected to echo the value.
     */
    private function setPHPCode(string $code): void
    {
        $this->value_escaped = '<?php ' . $code . " ?>\n";
    }

    /**
     * hide this attribute. It won't be generated.
     */
    public function hide(): void
    {
        $this->replacedState = self::HIDDEN;
    }

    /**
     * generate value of this attribute from variable
     */
    public function overwriteValueWithVariable(string $phpVariable): void
    {
        $this->replacedState = self::VALUE_REPLACED;
        $this->phpVariable = $phpVariable;
        $this->setPHPCode('echo ' . $phpVariable);
    }

    /**
     * generate complete syntax of this attribute using variable
     */
    public function overwriteFullWithVariable(string $phpVariable): void
    {
        $this->replacedState = self::FULLY_REPLACED;
        $this->phpVariable = $phpVariable;
        $this->setPHPCode('echo ' . $phpVariable);
    }

    /**
     * use any PHP code to generate this attribute's value
     */
    public function overwriteValueWithCode(string $code): void
    {
        $this->replacedState = self::VALUE_REPLACED;
        $this->phpVariable = null;
        $this->setPHPCode($code);
    }

    /**
     * if value was overwritten with variable, get its name
     */
    public function getOverwrittenVariableName(): string
    {
        return $this->phpVariable;
    }

    /**
     * whether getValueEscaped() returns real value or PHP code
     */
    public function getReplacedState(): int
    {
        return $this->replacedState;
    }
}
