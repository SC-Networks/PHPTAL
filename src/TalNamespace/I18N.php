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

use PhpTal\TalNamespaceAttributeContent;
use PhpTal\TalNamespaceAttributeSurround;

/**
 * @package PHPTAL
 */
class I18N extends Builtin
{
    public function __construct()
    {
        parent::__construct('i18n', Builtin::NS_I18N);
        $this->addAttribute(new TalNamespaceAttributeContent('translate', 5));
        $this->addAttribute(new TalNamespaceAttributeSurround('name', 5));
        $this->addAttribute(new TalNamespaceAttributeSurround('attributes', 10));
        $this->addAttribute(new TalNamespaceAttributeSurround('domain', 3));
    }
}
