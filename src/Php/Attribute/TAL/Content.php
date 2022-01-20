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
     * @throws ParserException
     * @throws PhpNotAllowedException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    public function before(CodeWriter $codewriter): void
    {
        $expression = $this->extractEchoType($this->expression);

        $code = $codewriter->evaluateExpression($expression);

        if (is_array($code)) {
            $this->generateChainedContent($codewriter, $code);
            return;
        }

        if ($code === TalesInternal::NOTHING_KEYWORD) {
            return;
        }

        if ($code === TalesInternal::DEFAULT_KEYWORD) {
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
    public function after(CodeWriter $codewriter): void
    {
    }

    /**
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    private function generateDefault(CodeWriter $codewriter): void
    {
        $this->phpelement->generateContent($codewriter, true);
    }

    /**
     * @param CodeWriter $codewriter
     * @param array<string> $code
     *
     * @return void
     * @throws PhpTalException
     */
    protected function generateChainedContent(CodeWriter $codewriter, array $code): void
    {
        // todo yep, indeed, this thing executes logic, a lot of it, in the constructor
        new TalesChainExecutor($codewriter, $code, $this);
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
     * @param TalesChainExecutor $executor
     *
     * @return void
     */
    public function talesChainNothingKeyword(TalesChainExecutor $executor): void
    {
        $executor->breakChain();
    }

    /**
     * @param TalesChainExecutor $executor
     *
     * @return void
     * @throws PhpTalException
     */
    public function talesChainDefaultKeyword(TalesChainExecutor $executor): void
    {
        $executor->doElse();
        $this->generateDefault($executor->getCodeWriter());
        $executor->breakChain();
    }
}
