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

namespace PhpTal\Php\Attribute\METAL;

use PhpTal\Exception\ParserException;
use PhpTal\Exception\PhpTalException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;
use ReflectionException;

/**
 * METAL Specification 1.0
 *
 *      argument ::= Name
 *
 * Example:
 *
 *      <table metal:define-macro="sidebar">
 *        <tr><th>Links</th></tr>
 *        <tr><td metal:define-slot="links">
 *          <a href="/">A Link</a>
 *        </td></tr>
 *      </table>
 *
 * PHPTAL: (access to slots may be renamed)
 *
 *  <?php function XXXX_macro_sidebar($tpl) { ? >
 *      <table>
 *        <tr><th>Links</th></tr>
 *        <tr>
 *        <?php if (isset($tpl->slots->links)): ? >
 *          <?php echo $tpl->slots->links ? >
 *        <?php else: ? >
 *        <td>
 *          <a href="/">A Link</a>
 *        </td></tr>
 *      </table>
 *  <?php } ? >
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class DefineSlot extends Attribute
{
    private ?string $tmp_var = null;

    /**
     * Called before element printing.
     *
     *
     * @throws ParserException
     * @throws PhpTalException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    public function before(CodeWriter $codewriter): void
    {
        $this->tmp_var = $codewriter->createTempVariable();

        $codewriter->doSetVar($this->tmp_var, $codewriter->interpolateTalesVarsInString($this->expression));
        $codewriter->doIf('$ctx->hasSlot('.$this->tmp_var.')');
        $codewriter->pushCode('$ctx->echoSlot('.$this->tmp_var.')');
        $codewriter->doElse();
    }

    /**
     * Called after element printing.
     *
     *
     * @throws PhpTalException
     */
    public function after(CodeWriter $codewriter): void
    {
        $codewriter->doEnd('if');

        $codewriter->recycleTempVariable((string) $this->tmp_var);
    }
}
