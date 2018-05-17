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
class Replace extends \PhpTal\Php\Attribute implements \PhpTal\Php\TalesChainReaderInterface
{
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        // tal:replace="" => do nothing and ignore node
        if (trim($this->expression) == "") {
            return;
        }

        $expression = $this->extractEchoType($this->expression);
        $code = $codewriter->evaluateExpression($expression);

        // chained expression
        if (is_array($code)) {
            return $this->replaceByChainedExpression($codewriter, $code);
        }

        // nothing do nothing
        if ($code == \PhpTal\Php\TalesInternal::NOTHING_KEYWORD) {
            return;
        }

        // default generate default tag content
        if ($code == \PhpTal\Php\TalesInternal::DEFAULT_KEYWORD) {
            return $this->generateDefault($codewriter);
        }

        // replace tag with result of expression
        $this->doEchoAttribute($codewriter, $code);
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
    }

    /**
     * support expressions like "foo | bar"
     */
    private function replaceByChainedExpression(\PhpTal\Php\CodeWriter $codewriter, $expArray)
    {
        $executor = new \PhpTal\Php\TalesChainExecutor(
            $codewriter, $expArray, $this
        );
    }

    public function talesChainNothingKeyword(\PhpTal\Php\TalesChainExecutor $executor)
    {
        $executor->continueChain();
    }

    public function talesChainDefaultKeyword(\PhpTal\Php\TalesChainExecutor $executor)
    {
        $executor->doElse();
        $this->generateDefault($executor->getCodeWriter());
        $executor->breakChain();
    }

    public function talesChainPart(\PhpTal\Php\TalesChainExecutor $executor, $exp, $islast)
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
     */
    private function generateDefault(\PhpTal\Php\CodeWriter $codewriter)
    {
        $this->phpelement->generateSurroundHead($codewriter);
        $this->phpelement->generateHead($codewriter);
        $this->phpelement->generateContent($codewriter);
        $this->phpelement->generateFoot($codewriter);
        $this->phpelement->generateSurroundFoot($codewriter);
    }
}
