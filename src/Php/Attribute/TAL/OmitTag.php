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
 * @version  SVN: $Id$
 * @link     http://phptal.org/
 */

namespace PhpTal\Php\Attribute\TAL;

/**
 * TAL Specifications 1.4
 *
 *   argument ::= [expression]
 *
 * Example:
 *
 *      <div tal:omit-tag="" comment="This tag will be removed">
 *          <i>...but this text will remain.</i>
 *      </div>
 *
 *      <b tal:omit-tag="not:bold">I may not be bold.</b>
 *
 * To leave the contents of a tag in place while omitting the surrounding
 * start and end tag, use the omit-tag statement.
 *
 * If its expression evaluates to a false value, then normal processing
 * of the element continues.
 *
 * If the expression evaluates to a true value, or there is no
 * expression, the statement tag is replaced with its contents. It is up to
 * the interface between TAL and the expression engine to determine the
 * value of true and false. For these purposes, the value nothing is false,
 * and cancellation of the action has the same effect as returning a
 * false value.
 *
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class OmitTag extends \PhpTal\Php\Attribute
{
    private $varname;

    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        if (trim($this->expression) == '') {
            $this->phpelement->headFootDisabled = true;
        } else {

            $this->varname = $codewriter->createTempVariable();

            // print tag header/foot only if condition is false
            $cond = $codewriter->evaluateExpression($this->expression);
            $this->phpelement->headPrintCondition = '('.$this->varname.' = !\PhpTal\Helper::phptal_unravel_closure('.$cond.'))';
            $this->phpelement->footPrintCondition = $this->varname;
        }
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
        if ($this->varname) $codewriter->recycleTempVariable($this->varname);
    }
}
