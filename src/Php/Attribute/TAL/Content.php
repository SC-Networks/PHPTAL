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
use PhpTal\Php\TalesChainReaderInterface;

/** TAL Specifications 1.4
 *
 *     argument ::= (['text'] | 'structure') expression
 *
 * Example:
 *
 *     <p tal:content="user/name">Fred Farkas</p>
 *
 *
 *
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Content extends Attribute implements TalesChainReaderInterface
{
    /**
     * Called before element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    public function before(CodeWriter $codewriter)
    {
        $expression = $this->extractEchoType($this->expression);

        $code = $codewriter->evaluateExpression($expression);

        if (is_array($code)) {
            $this->generateChainedContent($codewriter, $code);
            return;
        }

        if ($code === \PhpTal\Php\TalesInternal::NOTHING_KEYWORD) {
            return;
        }

        if ($code === \PhpTal\Php\TalesInternal::DEFAULT_KEYWORD) {
            $this->generateDefault($codewriter);
            return;
        }

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
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    private function generateDefault(CodeWriter $codewriter)
    {
        $this->phpelement->generateContent($codewriter, true);
    }

    /**
     * @param CodeWriter $codewriter
     * @param array $code
     *
     * @return void
     */
    protected function generateChainedContent(CodeWriter $codewriter, $code)
    {
        // todo yep, indeed, this thing executes logic, a lot of it, in the constructor
        new TalesChainExecutor($codewriter, $code, $this);
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
     * @param TalesChainExecutor $executor
     *
     * @return void
     */
    public function talesChainNothingKeyword(TalesChainExecutor $executor)
    {
        $executor->breakChain();
    }

    /**
     * @param TalesChainExecutor $executor
     *
     * @return void
     */
    public function talesChainDefaultKeyword(TalesChainExecutor $executor)
    {
        $executor->doElse();
        $this->generateDefault($executor->getCodeWriter());
        $executor->breakChain();
    }
}
