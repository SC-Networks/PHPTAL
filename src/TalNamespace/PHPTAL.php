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

use PhpTal\TalNamespaceAttributeSurround;

/**
 * @package PHPTAL
 */
class PHPTAL extends Builtin
{
    public function __construct()
    {
        parent::__construct('phptal', 'http://phptal.org/ns/phptal');
        $this->addAttribute(new TalNamespaceAttributeSurround('tales', -1));
        $this->addAttribute(new TalNamespaceAttributeSurround('debug', -2));
        $this->addAttribute(new TalNamespaceAttributeSurround('id', 7));
        $this->addAttribute(new TalNamespaceAttributeSurround('cache', -3));
    }
}
