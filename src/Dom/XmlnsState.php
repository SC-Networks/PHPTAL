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

use PhpTal\TalNamespace\Builtin;

/**
 * Stores XMLNS aliases fluctuation in the xml flow.
 *
 * This class is used to bind a PHPTAL namespace to an alias, for example using
 * xmlns:t="http://xml.zope.org/namespaces/tal" and later use t:repeat instead
 * of tal:repeat.
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class XmlnsState
{

    /**
     * @var array
     */
    private $prefix_to_uri;

    /**
     * @var mixed
     */
    private $current_default;

    /** Create a new XMLNS state inheriting provided aliases.
     * @param array $prefix_to_uri
     * @param $current_default
     */
    public function __construct(array $prefix_to_uri, $current_default)
    {
        $this->prefix_to_uri = $prefix_to_uri;
        $this->current_default = $current_default;
    }

    /**
     * @param $prefix
     *
     * @return bool|mixed|string
     */
    public function prefixToNamespaceURI($prefix)
    {
        if ($prefix === 'xmlns') {
            return Builtin::NS_XMLNS;
        }

        if ($prefix === 'xml') {
            return Builtin::NS_XML;
        }

        // domdefs provides fallback for all known phptal ns
        if (isset($this->prefix_to_uri[$prefix])) {
            return $this->prefix_to_uri[$prefix];
        }
        return Defs::getInstance()->prefixToNamespaceURI($prefix);
    }

    /**
     * Returns true if $attName is a valid attribute name, false otherwise.
     *
     * @param string $namespace_uri
     * @param string $local_name
     *
     * @return bool
     */
    public function isValidAttributeNS($namespace_uri, $local_name)
    {
        return Defs::getInstance()->isValidAttributeNS($namespace_uri, $local_name);
    }

    /**
     * @param string $namespace_uri
     *
     * @return bool
     */
    public function isHandledNamespace($namespace_uri)
    {
        return Defs::getInstance()->isHandledNamespace($namespace_uri);
    }

    /**
     * Returns a new XmlnsState inheriting of $this if $nodeAttributes contains
     * xmlns attributes, returns $this otherwise.
     *
     * This method is used by the PHPTAL parser to keep track of xmlns fluctuation for
     * each encountered node.
     *
     * @param array $nodeAttributes
     *
     * @return XmlnsState
     */
    public function newElement(array $nodeAttributes)
    {
        $prefix_to_uri = $this->prefix_to_uri;
        $current_default = $this->current_default;

        $changed = false;
        foreach ($nodeAttributes as $qname => $value) {
            if (preg_match('/^xmlns:(.+)$/', $qname, $m)) {
                $changed = true;
                list(, $prefix) = $m;
                $prefix_to_uri[$prefix] = $value;
            }

            if ($qname === 'xmlns') {
                $changed = true;
                $current_default = $value;
            }
        }

        if ($changed) {
            return new XmlnsState($prefix_to_uri, $current_default);
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCurrentDefaultNamespaceURI()
    {
        return $this->current_default;
    }
}
