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

use PhpTal\TalNamespaceAttributeContent;
use PhpTal\TalNamespaceAttributeReplace;
use PhpTal\TalNamespaceAttributeSurround;

/**
 * @package PHPTAL
 */
class TAL extends Builtin
{
    public function __construct()
    {
        parent::__construct('tal', Builtin::NS_TAL);
        $this->addAttribute(new TalNamespaceAttributeSurround('define', 4));
        $this->addAttribute(new TalNamespaceAttributeSurround('condition', 6));
        $this->addAttribute(new TalNamespaceAttributeSurround('repeat', 8));
        $this->addAttribute(new TalNamespaceAttributeContent('content', 11));
        $this->addAttribute(new TalNamespaceAttributeReplace('replace', 9));
        $this->addAttribute(new TalNamespaceAttributeSurround('attributes', 9));
        $this->addAttribute(new TalNamespaceAttributeSurround('omit-tag', 0));
        $this->addAttribute(new TalNamespaceAttributeSurround('comment', 12));
        $this->addAttribute(new TalNamespaceAttributeSurround('on-error', 2));
    }
}
