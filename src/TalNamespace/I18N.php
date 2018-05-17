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
class I18N extends Builtin
{
    public function __construct()
    {
        parent::__construct('i18n', 'http://xml.zope.org/namespaces/i18n');
        $this->addAttribute(new \PhpTal\TalNamespaceAttributeContent('translate', 5));
        $this->addAttribute(new \PhpTal\TalNamespaceAttributeSurround('name', 5));
        $this->addAttribute(new \PhpTal\TalNamespaceAttributeSurround('attributes', 10));
        $this->addAttribute(new \PhpTal\TalNamespaceAttributeSurround('domain', 3));
    }
}
