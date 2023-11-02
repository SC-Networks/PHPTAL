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

namespace PhpTal;

use PhpTal\Dom\Element;
use PhpTal\Exception\ConfigurationException;
use PhpTal\Php\Attribute;

/**
 * @see \PhpTal\TalNamespaceAttribute
 * @package PHPTAL
 */
abstract class TalNamespace
{
    private readonly string $prefix;

    private readonly string $namespace_uri;

    /**
     * @var array<string, TalNamespaceAttribute>
     */
    protected $attributes;

    /**
     * @throws Exception\ConfigurationException
     */
    public function __construct(string $prefix, string $namespace_uri)
    {
        if (trim($namespace_uri) === '' || trim($prefix) === '') {
            throw new ConfigurationException("Can't create namespace with empty prefix or namespace URI");
        }

        $this->attributes = [];
        $this->prefix = $prefix;
        $this->namespace_uri = $namespace_uri;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getNamespaceURI(): string
    {
        return $this->namespace_uri;
    }

    public function hasAttribute(string $attributeName): bool
    {
        return array_key_exists(strtolower($attributeName), $this->attributes);
    }

    /**
     * @return mixed
     */
    public function getAttribute(string $attributeName)
    {
        return $this->attributes[strtolower($attributeName)];
    }

    public function addAttribute(TalNamespaceAttribute $attribute): void
    {
        $attribute->setNamespace($this);
        $this->attributes[strtolower($attribute->getLocalName())] = $attribute;
    }

    /**
     * @return array<string, TalNamespaceAttribute>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    
    abstract public function createAttributeHandler(
        TalNamespaceAttribute $att,
        Element $tag,
        mixed $expression
    ): Attribute;
}
