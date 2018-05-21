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

namespace PhpTal\Php\Attribute\METAL;

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
class DefineSlot extends \PhpTal\Php\Attribute
{
    private $tmp_var;

    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $this->tmp_var = $codewriter->createTempVariable();

        $codewriter->doSetVar($this->tmp_var, $codewriter->interpolateTalesVarsInString($this->expression));
        $codewriter->doIf('$ctx->hasSlot('.$this->tmp_var.')');
        $codewriter->pushCode('$ctx->echoSlot('.$this->tmp_var.')');
        $codewriter->doElse();
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
        $codewriter->doEnd('if');

        $codewriter->recycleTempVariable($this->tmp_var);
    }
}