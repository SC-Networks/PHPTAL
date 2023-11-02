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

namespace PhpTal\TalNamespace;

use PhpTal\Dom\Element;
use PhpTal\Php\Attribute;
use PhpTal\TalNamespace;
use PhpTal\TalNamespaceAttribute;

/**
 * @package PHPTAL
 */
class Builtin extends TalNamespace
{
    final public const NS_METAL = 'http://xml.zope.org/namespaces/metal';
    final public const NS_TAL = 'http://xml.zope.org/namespaces/tal';
    final public const NS_I18N = 'http://xml.zope.org/namespaces/i18n';
    final public const NS_XML = 'http://www.w3.org/XML/1998/namespace';
    final public const NS_XMLNS = 'http://www.w3.org/2000/xmlns/';
    final public const NS_XHTML = 'http://www.w3.org/1999/xhtml';

    /**
     * @param mixed $expression
     *
     */
    public function createAttributeHandler(TalNamespaceAttribute $att, Element $tag, $expression): Attribute
    {
        $name = $att->getLocalName();

        // change define-macro to "define macro" and capitalize words
        $name = str_replace(' ', '', ucwords(str_replace('-', ' ', $name)));

        // case is important when using autoload on case-sensitive filesystems
        /** @var class-string<Attribute> $class */
        $class = 'PhpTal\\Php\\Attribute\\'.strtoupper($this->getPrefix()).'\\'.$name;

        return new $class($tag, $expression);
    }
}
