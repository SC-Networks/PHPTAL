<?php
declare(strict_types=1);

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

use PhpTal\TalNamespaceAttributeReplace;
use PhpTal\TalNamespaceAttributeSurround;

/**
 * @package PHPTAL
 */
class METAL extends Builtin
{
    public function __construct()
    {
        parent::__construct('metal', Builtin::NS_METAL);
        $this->addAttribute(new TalNamespaceAttributeSurround('define-macro', 1));
        $this->addAttribute(new TalNamespaceAttributeReplace('use-macro', 9));
        $this->addAttribute(new TalNamespaceAttributeSurround('define-slot', 9));
        $this->addAttribute(new TalNamespaceAttributeSurround('fill-slot', 9));
    }
}
