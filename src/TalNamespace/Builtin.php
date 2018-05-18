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

namespace PhpTal\TalNamespace;

/**
 * @package PHPTAL
 */
class Builtin extends \PhpTal\TalNamespace
{

    const NS_METAL = 'http://xml.zope.org/namespaces/metal';
    const NS_TAL = 'http://xml.zope.org/namespaces/tal';
    const NS_I18N = 'http://xml.zope.org/namespaces/i18n';
    const NS_XML = 'http://www.w3.org/XML/1998/namespace';
    const NS_XMLNS = 'http://www.w3.org/2000/xmlns/';
    const NS_XHTML = 'http://www.w3.org/1999/xhtml';

    /**
     * @param \PhpTal\TalNamespaceAttribute $att
     * @param \PhpTal\Dom\Element $tag
     * @param mixed $expression
     *
     * @return \PhpTal\Php\Attribute
     */
    public function createAttributeHandler(\PhpTal\TalNamespaceAttribute $att, \PhpTal\Dom\Element $tag, $expression)
    {
        $name = $att->getLocalName();

        // change define-macro to "define macro" and capitalize words
        $name = str_replace(' ', '', ucwords(strtr($name, '-', ' ')));

        // case is important when using autoload on case-sensitive filesystems
            $class = 'PhpTal\\Php\\Attribute\\'.strtoupper($this->getPrefix()).'\\'.$name;

        return new $class($tag, $expression);
    }
}
