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
use PhpTal\Exception\TemplateException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;
use PhpTal\Php\TalesChainExecutor;
use PhpTal\Php\TalesChainReaderInterface;
use PhpTal\Php\TalesInternal;
use ReflectionException;

/**
 * TAL spec 1.4 for tal:define content
 *
 * argument       ::= define_scope [';' define_scope]*
 * define_scope   ::= (['local'] | 'global') define_var
 * define_var     ::= variable_name expression
 * variable_name  ::= Name
 *
 * Note: If you want to include a semi-colon (;) in an expression, it must be escaped by doubling it (;;).*
 *
 * examples:
 *
 *   tal:define="mytitle template/title; tlen python:len(mytitle)"
 *   tal:define="global company_name string:Digital Creations, Inc."
 *
 *
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Define extends Attribute implements TalesChainReaderInterface
{
    /**
     * @var string
     */
    private $tmp_content_var;

    /**
     * @var bool
     */
    private $buffered = false;

    /**
     * @var string
     */
    private $defineScope;

    /**
     * @var null|string
     */
    private $defineVar;

    /**
     * @var bool
     */
    private $pushedContext = false;

    /**
     * Prevents generation of invalid PHP code when given invalid TALES
     * @var bool
     */
    private $chainPartGenerated = false;

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
        $expressions = $codewriter->splitExpression($this->expression);
        $definesAnyNonGlobalVars = false;

        foreach ($expressions as $exp) {
            [$defineScope, $defineVar, $expression] = $this->parseExpression($exp);
            if (!$defineVar) {
                continue;
            }

            $this->defineScope = $defineScope;

            // <span tal:define="global foo" /> should be invisible, but <img tal:define="bar baz" /> not
            if ($defineScope !== 'global') {
                $definesAnyNonGlobalVars = true;
            }

            if ($this->defineScope !== 'global' && !$this->pushedContext) {
                $codewriter->pushContext();
                $this->pushedContext = true;
            }

            $this->defineVar = $defineVar;
            if ($expression === null) {
                // no expression give, use content of tag as value for newly defined var.
                $this->bufferizeContent($codewriter);
                continue;
            }

            $code = $codewriter->evaluateExpression($expression);
            if (is_array($code)) {
                $this->chainedDefine($codewriter, $code);
            } elseif ($code === TalesInternal::NOTHING_KEYWORD) {
                $this->doDefineVarWith($codewriter, 'null');
            } else {
                $this->doDefineVarWith($codewriter, $code);
            }
        }

        // if the content of the tag was buffered or the tag has nothing to tell, we hide it.
        if ($this->buffered || (!$definesAnyNonGlobalVars && !$this->phpelement->hasRealContent() && !$this->phpelement->hasRealAttributes())) {
            $this->phpelement->hidden = true;
        }
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
        if ($this->tmp_content_var) {
            $codewriter->recycleTempVariable($this->tmp_content_var);
        }
        if ($this->pushedContext) {
            $codewriter->popContext();
        }
    }

    /**
     * @param CodeWriter $codewriter
     * @param array $parts
     *
     * @return void
     * @throws PhpTalException
     */
    private function chainedDefine(CodeWriter $codewriter, $parts): void
    {
        new TalesChainExecutor($codewriter, $parts, $this);
    }

    /**
     * @param TalesChainExecutor $executor
     *
     * @return void
     * @throws TemplateException
     * @throws PhpTalException
     */
    public function talesChainNothingKeyword(TalesChainExecutor $executor): void
    {
        if (!$this->chainPartGenerated) {
            throw new TemplateException(
                'Invalid expression in tal:define',
                $this->phpelement->getSourceFile(),
                $this->phpelement->getSourceLine()
            );
        }

        $executor->doElse();
        $this->doDefineVarWith($executor->getCodeWriter(), 'null');
        $executor->breakChain();
    }

    /**
     * @param TalesChainExecutor $executor
     *
     * @return void
     * @throws TemplateException
     * @throws PhpTalException
     */
    public function talesChainDefaultKeyword(TalesChainExecutor $executor): void
    {
        if (!$this->chainPartGenerated) {
            throw new TemplateException(
                'Invalid expression in tal:define',
                $this->phpelement->getSourceFile(),
                $this->phpelement->getSourceLine()
            );
        }

        $executor->doElse();
        $this->bufferizeContent($executor->getCodeWriter());
        $executor->breakChain();
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
        $this->chainPartGenerated = true;

        if ($this->defineScope === 'global') {
            $var = '$tpl->getGlobalContext()->' . $this->defineVar;
        } else {
            $var = '$ctx->' . $this->defineVar;
        }

        $cw = $executor->getCodeWriter();

        if (!$islast) {
            // must use temp variable, because expression could refer to itself
            $tmp = $cw->createTempVariable();
            $executor->doIf('(' . $tmp . ' = ' . $expression . ') !== null');
            $cw->doSetVar($var, $tmp);
            $cw->recycleTempVariable($tmp);
        } else {
            $executor->doIf('(' . $var . ' = ' . $expression . ') !== null');
        }
    }

    /**
     * Parse the define expression, already splitted in sub parts by ';'.
     *
     * @param string $exp
     *
     * @return array
     */
    public function parseExpression(string $exp): array
    {
        $defineScope = false; // (local | global)

        // extract defineScope from expression
        $exp = trim($exp);
        if (preg_match('/^(local|global)\s+(.*?)$/ism', $exp, $m)) {
            [, $defineScope, $exp] = $m;
            $exp = trim($exp);
        }

        // extract varname and expression from remaining of expression
        [$defineVar, $newExp] = $this->parseSetExpression($exp);
        if ($newExp !== null) {
            $newExp = trim($newExp);
        }
        return [$defineScope, $defineVar, $newExp];
    }

    /**
     * @param CodeWriter $codewriter
     *
     * @return void
     */
    private function bufferizeContent(CodeWriter $codewriter): void
    {
        if (!$this->buffered) {
            $this->tmp_content_var = $codewriter->createTempVariable();
            $codewriter->pushCode('ob_start()');
            $this->phpelement->generateContent($codewriter);
            $codewriter->doSetVar($this->tmp_content_var, 'ob_get_clean()');
            $this->buffered = true;
        }
        $this->doDefineVarWith($codewriter, $this->tmp_content_var);
    }

    /**
     * @param CodeWriter $codewriter
     * @param string $code
     *
     * @return void
     */
    private function doDefineVarWith(CodeWriter $codewriter, string $code): void
    {
        if ($this->defineScope === 'global') {
            $codewriter->doSetVar('$tpl->getGlobalContext()->' . $this->defineVar, $code);
        } else {
            $codewriter->doSetVar('$ctx->' . $this->defineVar, $code);
        }
    }
}
