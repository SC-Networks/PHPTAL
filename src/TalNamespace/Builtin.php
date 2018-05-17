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
