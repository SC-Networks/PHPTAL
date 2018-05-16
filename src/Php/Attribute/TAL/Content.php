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
class Content
extends \PhpTal\Php\Attribute
implements \PhpTal\Php\TalesChainReader
{
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $expression = $this->extractEchoType($this->expression);

        $code = $codewriter->evaluateExpression($expression);

        if (is_array($code)) {
            return $this->generateChainedContent($codewriter, $code);
        }

        if ($code == \PhpTal\Php\TalesInternal::NOTHING_KEYWORD) {
            return;
        }

        if ($code == \PhpTal\Php\TalesInternal::DEFAULT_KEYWORD) {
            return $this->generateDefault($codewriter);
        }

        $this->doEchoAttribute($codewriter, $code);
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
    }

    private function generateDefault(\PhpTal\Php\CodeWriter $codewriter)
    {
        $this->phpelement->generateContent($codewriter, true);
    }

    protected function generateChainedContent(\PhpTal\Php\CodeWriter $codewriter, $code)
    {
        $executor = new \PhpTal\Php\TalesChainExecutor($codewriter, $code, $this);
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

    public function talesChainNothingKeyword(\PhpTal\Php\TalesChainExecutor $executor)
    {
        $executor->breakChain();
    }

    public function talesChainDefaultKeyword(\PhpTal\Php\TalesChainExecutor $executor)
    {
        $executor->doElse();
        $this->generateDefault($executor->getCodeWriter());
        $executor->breakChain();
    }
}
