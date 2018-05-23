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

use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;
use PhpTal\Php\TalesChainExecutor;

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
class Replace extends Attribute implements \PhpTal\Php\TalesChainReaderInterface
{
    /**
     * Called before element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws \PhpTal\Exception\PhpTalException
     * @throws \ReflectionException
     */
    public function before(CodeWriter $codewriter)
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
        if ($code === \PhpTal\Php\TalesInternal::NOTHING_KEYWORD) {
            return;
        }

        // default generate default tag content
        if ($code === \PhpTal\Php\TalesInternal::DEFAULT_KEYWORD) {
            $this->generateDefault($codewriter);
            return;
        }

        // replace tag with result of expression
        $this->doEchoAttribute($codewriter, $code);
    }

    /**
     * Called after element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    public function after(CodeWriter $codewriter)
    {
    }

    /**
     * support expressions like "foo | bar"
     *
     * @param CodeWriter $codewriter
     * @param array $expArray
     *
     * @throws \PhpTal\Exception\PhpTalException
     */
    private function replaceByChainedExpression(CodeWriter $codewriter, $expArray)
    {
        new TalesChainExecutor($codewriter, $expArray, $this);
    }

    /**
     * @param TalesChainExecutor $executor
     *
     * @return void
     */
    public function talesChainNothingKeyword(TalesChainExecutor $executor)
    {
        $executor->continueChain();
    }

    /**
     * @param TalesChainExecutor $executor
     *
     * @return void
     * @throws \PhpTal\Exception\PhpTalException
     */
    public function talesChainDefaultKeyword(TalesChainExecutor $executor)
    {
        $executor->doElse();
        $this->generateDefault($executor->getCodeWriter());
        $executor->breakChain();
    }

    /**
     * @param TalesChainExecutor $executor
     * @param string $exp
     * @param bool $islast
     *
     * @return void
     * @throws \PhpTal\Exception\PhpTalException
     */
    public function talesChainPart(TalesChainExecutor $executor, $exp, $islast)
    {
        if (!$islast) {
            $var = $executor->getCodeWriter()->createTempVariable();
            $executor->doIf('!\PhpTal\Helper::phptal_isempty('.$var.' = '.$exp.')');
            $this->doEchoAttribute($executor->getCodeWriter(), $var);
            $executor->getCodeWriter()->recycleTempVariable($var);
        } else {
            $executor->doElse();
            $this->doEchoAttribute($executor->getCodeWriter(), $exp);
        }
    }

    /**
     * don't replace - re-generate default content
     * @param CodeWriter $codewriter
     *
     * @throws \PhpTal\Exception\PhpTalException
     */
    private function generateDefault(CodeWriter $codewriter)
    {
        $this->phpelement->generateSurroundHead($codewriter);
        $this->phpelement->generateHead($codewriter);
        $this->phpelement->generateContent($codewriter);
        $this->phpelement->generateFoot($codewriter);
        $this->phpelement->generateSurroundFoot($codewriter);
    }
}
