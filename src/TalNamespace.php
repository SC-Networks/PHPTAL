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

namespace PhpTal;

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
    public function __construct($prefix, $namespace_uri)
    {
        if (!$namespace_uri || !$prefix) {
            throw new Exception\ConfigurationException("Can't create namespace with empty prefix or namespace URI");
        }

        $this->attributes = [];
        $this->prefix = $prefix;
        $this->namespace_uri = $namespace_uri;
    }

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getNamespaceURI()
    {
        return $this->namespace_uri;
    }

    /**
     * @param $attributeName
     * @return bool
     */
    public function hasAttribute($attributeName)
    {
        return array_key_exists(strtolower($attributeName), $this->attributes);
    }

    /**
     * @param $attributeName
     * @return mixed
     */
    public function getAttribute($attributeName)
    {
        return $this->attributes[strtolower($attributeName)];
    }

    /**
     * @param TalNamespaceAttribute $attribute
     * @return void
     */
    public function addAttribute(TalNamespaceAttribute $attribute)
    {
        $attribute->setNamespace($this);
        $this->attributes[strtolower($attribute->getLocalName())] = $attribute;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param TalNamespaceAttribute $att
     * @param Dom\Element $tag
     * @param mixed $expression
     * @return Php\Attribute
     */
    abstract public function createAttributeHandler(TalNamespaceAttribute $att, Dom\Element $tag, $expression);
}
