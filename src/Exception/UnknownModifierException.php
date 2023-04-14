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

namespace PhpTal\Exception;

/**
 * ${unknown:foo} found in template
 *
 * @package PHPTAL
 */
class UnknownModifierException extends TemplateException
{
    /**
     * UnknownModifierException constructor.
     * @param string $modifier_name
     */
    public function __construct(string $msg, private ?string $modifier_name = null)
    {
        parent::__construct($msg);
    }

    public function getModifierName(): ?string
    {
        return $this->modifier_name;
    }
}
