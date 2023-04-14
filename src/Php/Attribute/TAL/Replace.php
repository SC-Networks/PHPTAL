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
 *      argument ::= (['text'] | 'structure') expression
 *
 *  Default behaviour : text
 *
 *      <span tal:replace="template/title">Title</span>
 *      <span tal:replace="text template/title">Title</span>
 *      <span tal:replace="structure table" />
 *      <span tal:replace="nothing">This element is a comment.</span>
 *
 *
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Replace extends Attribute implements TalesChainReaderInterface
{
    /**
     * Called before element printing.
     *
     *
     * @throws ParserException
     * @throws PhpNotAllowedException
     * @throws PhpTalException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    public function before(CodeWriter $codewriter): void
    {
        // tal:replace="" => do nothing and ignore node
        if (trim($this->expression) === '') {
            return;
        }

        $expression = $this->extractEchoType($this->expression);
        $code = $codewriter->evaluateExpression($expression);

        // chained expression
        if (is_array($code)) {
            $this->replaceByChainedExpression($codewriter, $code);
            return;
        }

        // nothing do nothing
        if ($code === TalesInternal::NOTHING_KEYWORD) {
            return;
        }

        // default generate default tag content
        if ($code === TalesInternal::DEFAULT_KEYWORD) {
            $this->generateDefault($codewriter);
            return;
        }

        // replace tag with result of expression
        $this->doEchoAttribute($codewriter, $code);
    }

    /**
     * Called after element printing.
     *
     *
     */
    public function after(CodeWriter $codewriter): void
    {
    }

    /**
     * support expressions like "foo | bar"
     *
     * @param array<string> $expArray
     * @throws PhpTalException
     */
    private function replaceByChainedExpression(CodeWriter $codewriter, $expArray): void
    {
        new TalesChainExecutor($codewriter, $expArray, $this);
    }

    public function talesChainNothingKeyword(TalesChainExecutor $executor): void
    {
        $executor->continueChain();
    }

    /**
     * @throws PhpTalException
     */
    public function talesChainDefaultKeyword(TalesChainExecutor $executor): void
    {
        $executor->doElse();
        $this->generateDefault($executor->getCodeWriter());
        $executor->breakChain();
    }

    /**
     *
     * @throws PhpTalException
     */
    public function talesChainPart(TalesChainExecutor $executor, string $expression, bool $islast): void
    {
        if (!$islast) {
            $var = $executor->getCodeWriter()->createTempVariable();
            $executor->doIf('!\PhpTal\Helper::phptal_isempty('.$var.' = '.$expression.')');
            $this->doEchoAttribute($executor->getCodeWriter(), $var);
            $executor->getCodeWriter()->recycleTempVariable($var);
        } else {
            $executor->doElse();
            $this->doEchoAttribute($executor->getCodeWriter(), $expression);
        }
    }

    /**
     * don't replace - re-generate default content
     *
     * @throws PhpTalException
     */
    private function generateDefault(CodeWriter $codewriter): void
    {
        $this->phpelement->generateSurroundHead($codewriter);
        $this->phpelement->generateHead($codewriter);
        $this->phpelement->generateContent($codewriter);
        $this->phpelement->generateFoot($codewriter);
        $this->phpelement->generateSurroundFoot($codewriter);
    }
}
