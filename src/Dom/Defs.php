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

use PhpTal\TalNamespace;
use PhpTal\TalNamespace\Builtin;
use PhpTal\TalNamespace\I18N;
use PhpTal\TalNamespace\METAL;
use PhpTal\TalNamespace\PHPTAL;
use PhpTal\TalNamespace\TAL;
use PhpTal\TalNamespaceAttribute;

/**
 * PHPTAL constants.
 *
 * This is a pseudo singleton class, a user may decide to provide
 * his own singleton instance which will then be used by PHPTAL.
 *
 * This behaviour is mainly useful to remove builtin namespaces
 * and provide custom ones.
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Defs
{

    /**
     * @var Defs|null
     */
    private static $instance;

    /**
     * @var array
     */
    private $dictionary = [];

    /**
     * list of \PhpTal\TalNamespace objects
     */
    private $namespaces_by_uri = [];

    /**
     * @var array
     */
    private $prefix_to_uri = [
        'xml' => Builtin::NS_XML,
        'xmlns' => Builtin::NS_XMLNS,
    ];

    /**
     * This array contains XHTML tags that must be echoed in a &lt;tag/&gt; form
     * instead of the &lt;tag&gt;&lt;/tag&gt; form.
     *
     * In fact, some browsers does not support the later form so PHPTAL
     * ensure these tags are correctly echoed.
     */
    private static $XHTML_EMPTY_TAGS = [
        'area',
        'base',
        'basefont',
        'br',
        'col',
        'command',
        'embed',
        'frame',
        'hr',
        'img',
        'input',
        'isindex',
        'keygen',
        'link',
        'meta',
        'param',
        'wbr',
        'source',
        'track',
    ];

    /**
     * This array contains XHTML boolean attributes, their value is self
     * contained (ie: they are present or not).
     */
    private static $XHTML_BOOLEAN_ATTRIBUTES = [
        'autoplay',
        'async',
        'autofocus',
        'checked',
        'compact',
        'controls',
        'declare',
        'default',
        'defer',
        'disabled',
        'formnovalidate',
        'hidden',
        'ismap',
        'itemscope',
        'loop',
        'multiple',
        'noresize',
        'noshade',
        'novalidate',
        'nowrap',
        'open',
        'pubdate',
        'readonly',
        'required',
        'reversed',
        'scoped',
        'seamless',
        'selected',
    ];

    /**
     * this is a singleton
     */
    public static function getInstance(): Defs
    {
        if (!self::$instance) {
            self::$instance = new Defs();
        }
        return self::$instance;
    }

    protected function __construct()
    {
        $this->registerNamespace(new TAL());
        $this->registerNamespace(new METAL());
        $this->registerNamespace(new I18N());
        $this->registerNamespace(new PHPTAL());
    }

    /**
     * true if it's empty in XHTML (e.g. <img/>)
     * it will assume elements with no namespace may be XHTML too.
     *
     * @param string $namespace_uri
     * @param string $local_name local name of the tag
     *
     * @return bool
     */
    public function isEmptyTagNS(string $namespace_uri, string $local_name): bool
    {
        return ($namespace_uri === Builtin::NS_XHTML || $namespace_uri === '')
            && in_array(strtolower($local_name), self::$XHTML_EMPTY_TAGS);
    }

    /**
     * gives namespace URI for given registered (built-in) prefix
     *
     * @param string $prefix
     *
     * @return string
     */
    public function prefixToNamespaceURI($prefix): ?string
    {
        return $this->prefix_to_uri[$prefix] ?? null;
    }

    /**
     * gives typical prefix for given (built-in) namespace
     *
     * @param string $uri
     *
     * @return bool
     */
    public function namespaceURIToPrefix($uri): bool
    {
        return (bool) array_search($uri, $this->prefix_to_uri, true);
    }

    /**
     * array prefix => uri for prefixes that don't have to be declared in PHPTAL
     *
     * @return array
     */
    public function getPredefinedPrefixes(): array
    {
        return $this->prefix_to_uri;
    }

    /**
     * Returns true if the attribute is an xhtml boolean attribute.
     *
     * @param string $att local name
     *
     * @return bool
     */
    public function isBooleanAttribute(string $att): bool
    {
        return in_array($att, self::$XHTML_BOOLEAN_ATTRIBUTES);
    }

    /**
     * true if elements content is parsed as CDATA in text/html
     * and also accepts /* * / as comments.
     *
     * @param string $namespace_uri
     * @param string $local_name
     *
     * @return bool
     */
    public function isCDATAElementInHTML(string $namespace_uri, string $local_name): bool
    {
        return ($local_name === 'script' || $local_name === 'style')
            && ($namespace_uri === Builtin::NS_XHTML || $namespace_uri === '');
    }

    /**
     * Returns true if the attribute is a valid phptal attribute
     *
     * Examples of valid attributes: tal:content, metal:use-slot
     * Examples of invalid attributes: tal:unknown, metal:content
     *
     * @param string $namespace_uri
     * @param string $local_name
     *
     * @return bool
     */
    public function isValidAttributeNS(string $namespace_uri, string $local_name): bool
    {
        if (!$this->isHandledNamespace($namespace_uri)) {
            return false;
        }

        $attrs = $this->namespaces_by_uri[$namespace_uri]->getAttributes();
        return isset($attrs[$local_name]);
    }

    /**
     * is URI registered (built-in) namespace
     *
     * @param string $namespace_uri
     *
     * @return bool
     */
    public function isHandledNamespace(string $namespace_uri): bool
    {
        return isset($this->namespaces_by_uri[$namespace_uri]);
    }

    /**
     * Returns true if the attribute is a phptal handled xml namespace
     * declaration.
     *
     * Examples of handled xmlns:  xmlns:tal, xmlns:metal
     *
     * @param string $qname
     * @param string $value
     *
     * @return bool
     */
    public function isHandledXmlNs(string $qname, string $value): bool
    {
        return stripos($qname, 'xmlns:') === 0 && $this->isHandledNamespace($value);
    }

    /**
     * return objects that holds information about given TAL attribute
     *
     * @param string $namespace_uri
     * @param string $local_name
     *
     * @return TalNamespaceAttribute
     */
    public function getNamespaceAttribute(string $namespace_uri, string $local_name): TalNamespaceAttribute
    {
        $attrs = $this->namespaces_by_uri[$namespace_uri]->getAttributes();
        return $attrs[$local_name];
    }

    /**
     * Register a \PhpTal\TalNamespace and its attribute into PHPTAL.
     *
     * @param TalNamespace $ns
     */
    public function registerNamespace(TalNamespace $ns): void
    {
        $this->namespaces_by_uri[$ns->getNamespaceURI()] = $ns;
        $this->prefix_to_uri[$ns->getPrefix()] = $ns->getNamespaceURI();
        $prefix = strtolower($ns->getPrefix());
        foreach ($ns->getAttributes() as $name => $attribute) {
            $key = $prefix . ':' . strtolower($name);
            $this->dictionary[$key] = $attribute;
        }
    }
}
