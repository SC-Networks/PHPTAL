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
class METAL extends Builtin
{
    public function __construct()
    {
        parent::__construct('metal', 'http://xml.zope.org/namespaces/metal');
        $this->addAttribute(new \PhpTal\TalNamespaceAttributeSurround('define-macro', 1));
        $this->addAttribute(new \PhpTal\TalNamespaceAttributeReplace('use-macro', 9));
        $this->addAttribute(new \PhpTal\TalNamespaceAttributeSurround('define-slot', 9));
        $this->addAttribute(new \PhpTal\TalNamespaceAttributeSurround('fill-slot', 9));
    }
}
