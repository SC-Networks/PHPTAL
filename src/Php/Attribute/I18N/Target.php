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

namespace PhpTal\Php\Attribute\I18N;

use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;

/**
 * i18n:target
 *
 * The i18n:target attribute specifies the language of the translation we
 * want to get. If the value is "default", the language negotiation services
 * will be used to choose the destination language. If the value is
 * "nothing", no translation will be performed; this can be used to suppress
 * translation within a larger translated unit. Any other value must be a
 * language code.
 *
 * The attribute value is a TALES expression; the result of evaluating the
 * expression is the language code or one of the reserved values.
 *
 * Note that i18n:target is primarily used for hints to text extraction
 * tools and translation teams. If you had some text that should only be
 * translated to e.g. German, then it probably shouldn't be wrapped in an
 * i18n:translate span.
 *
 *
 * @package PHPTAL
 */
class Target extends Attribute
{
    /**
     * Called before element printing.
     *
     *
     */
    public function before(CodeWriter $codewriter): void
    {
    }

    /**
     * Called after element printing.
     *
     *
     */
    public function after(CodeWriter $codewriter): void
    {
    }
}
