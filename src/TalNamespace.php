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

use PhpTal\Php\Attribute;

/**
 * @see \PhpTal\TalNamespaceAttribute
 * @package PHPTAL
 */
abstract class TalNamespace
{
    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $namespace_uri;

    /**
     * @var array
     */
    protected $attributes;

    /**
     * TalNamespace constructor.
     *
     * @param string $prefix
     * @param string $namespace_uri
     * @throws Exception\ConfigurationException
     */
    public function __construct(string $prefix, string $namespace_uri)
    {
        if (trim($namespace_uri) === '' || trim($prefix) === '') {
            throw new Exception\ConfigurationException("Can't create namespace with empty prefix or namespace URI");
        }

        $this->attributes = [];
        $this->prefix = $prefix;
        $this->namespace_uri = $namespace_uri;
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getNamespaceURI(): string
    {
        return $this->namespace_uri;
    }

    /**
     * @param string $attributeName
     *
     * @return bool
     */
    public function hasAttribute(string $attributeName): bool
    {
        return array_key_exists(strtolower($attributeName), $this->attributes);
    }

    /**
     * @param string $attributeName
     *
     * @return mixed
     */
    public function getAttribute(string $attributeName)
    {
        return $this->attributes[strtolower($attributeName)];
    }

    /**
     * @param TalNamespaceAttribute $attribute
     *
     * @return void
     */
    public function addAttribute(TalNamespaceAttribute $attribute): void
    {
        $attribute->setNamespace($this);
        $this->attributes[strtolower($attribute->getLocalName())] = $attribute;
    }

    /**
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @param TalNamespaceAttribute $att
     * @param Dom\Element $tag
     * @param mixed $expression
     *
     * @return Php\Attribute
     */
    abstract public function createAttributeHandler(
        TalNamespaceAttribute $att,
        Dom\Element $tag,
        $expression
    ): Attribute;
}
