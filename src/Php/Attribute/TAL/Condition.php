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

namespace PhpTal\Php\Attribute\TAL;

use PhpTal\Exception\ParserException;
use PhpTal\Php\CodeWriter;
use PhpTal\Php\TalesChainExecutor;
use PhpTal\Php\TalesInternal;

/**
 * TAL Specifications 1.4
 *
 *      argument ::= expression
 *
 * Example:
 *
 *      <p tal:condition="here/copyright"
 *         tal:content="here/copyright">(c) 2000</p>
 *
 *
 *
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Condition extends \PhpTal\Php\Attribute implements \PhpTal\Php\TalesChainReaderInterface
{
    /**
     * @var array
     */
    private $expressions = [];

    /**
     * Called before element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    public function before(CodeWriter $codewriter)
    {
        $code = $codewriter->evaluateExpression($this->expression);

        // If it's a chained expression build a new code path
        if (is_array($code)) {
            $this->expressions = [];
            new TalesChainExecutor($codewriter, $code, $this);
            return;
        }

        // Force a falsy condition if the nothing keyword is active
        if ($code === TalesInternal::NOTHING_KEYWORD) {
            $code = 'false';
        }

        $codewriter->doIf('\PhpTal\Helper::phptal_true(' . $code . ')');
    }

    /**
     * Called after element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws \PhpTal\Exception\PhpTalException
     */
    public function after(CodeWriter $codewriter)
    {
        $codewriter->doEnd('if');
    }


    /**
     * @param TalesChainExecutor $executor
     * @param string $exp
     * @param bool $islast
     *
     * @return void
     */
    public function talesChainPart(TalesChainExecutor $executor, $exp, $islast)
    {
        // check if the expression is empty
        if ($exp !== 'false') {
            $this->expressions[] = '!\PhpTal\Helper::phptal_isempty(' . $exp . ')';
        }

        if ($islast) {
            // for the last one in the chain build a ORed condition
            $executor->getCodeWriter()->doIf(implode(' || ', $this->expressions));
            // The executor will always end an if so we output a dummy if
            $executor->doIf('false');
        }
    }

    /**
     * @param TalesChainExecutor $executor
     *
     * @return void
     */
    public function talesChainNothingKeyword(TalesChainExecutor $executor)
    {
        // end the chain
        $this->talesChainPart($executor, 'false', true);
        $executor->breakChain();
    }

    /**
     * @param TalesChainExecutor $executor
     *
     * @return void
     * @throws ParserException
     */
    public function talesChainDefaultKeyword(TalesChainExecutor $executor)
    {
        throw new ParserException(
            '\'default\' keyword not allowed on conditional expressions',
            $this->phpelement->getSourceFile(),
            $this->phpelement->getSourceLine()
        );
    }
}
