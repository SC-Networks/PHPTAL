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

namespace PhpTal\Php\Attribute\TAL;

use PhpTal\Exception\ParserException;
use PhpTal\Exception\PhpNotAllowedException;
use PhpTal\Exception\PhpTalException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;
use PhpTal\Php\TalesChainExecutor;
use PhpTal\Php\TalesChainReaderInterface;
use PhpTal\Php\TalesInternal;
use ReflectionException;

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
class Condition extends Attribute implements TalesChainReaderInterface
{
    /** @var array<string> */
    private $expressions = [];

    /**
     * Called before element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws ParserException
     * @throws PhpNotAllowedException
     * @throws PhpTalException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    public function before(CodeWriter $codewriter): void
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
     * @throws PhpTalException
     */
    public function after(CodeWriter $codewriter): void
    {
        $codewriter->doEnd('if');
    }


    /**
     * @param TalesChainExecutor $executor
     * @param string $expression
     * @param bool $islast
     *
     * @return void
     * @throws PhpTalException
     */
    public function talesChainPart(TalesChainExecutor $executor, string $expression, bool $islast): void
    {
        // check if the expression is empty
        if ($expression !== 'false') {
            $this->expressions[] = '!\PhpTal\Helper::phptal_isempty(' . $expression . ')';
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
     * @throws PhpTalException
     */
    public function talesChainNothingKeyword(TalesChainExecutor $executor): void
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
    public function talesChainDefaultKeyword(TalesChainExecutor $executor): void
    {
        throw new ParserException(
            '\'default\' keyword not allowed on conditional expressions',
            $this->phpelement->getSourceFile(),
            $this->phpelement->getSourceLine()
        );
    }
}
