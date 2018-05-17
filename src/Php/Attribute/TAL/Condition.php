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
    private $expressions = array();

    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $code = $codewriter->evaluateExpression($this->expression);

        // If it's a chained expression build a new code path
        if (is_array($code)) {
            $this->expressions = array();
            $executor = new \PhpTal\Php\TalesChainExecutor($codewriter, $code, $this);
            return;
        }

        // Force a falsy condition if the nothing keyword is active
        if ($code == \PhpTal\Php\TalesInternal::NOTHING_KEYWORD) {
            $code = 'false';
        }

        $codewriter->doIf('\PhpTal\Helper::phptal_true(' . $code . ')');
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
        $codewriter->doEnd('if');
    }


    public function talesChainPart(\PhpTal\Php\TalesChainExecutor $executor, $exp, $islast)
    {
        // check if the expression is empty
        if ($exp !== 'false') {
            $this->expressions[] = '!\PhpTal\Helper::phptal_isempty(' . $exp . ')';
        }

        if ($islast) {
            // for the last one in the chain build a ORed condition
            $executor->getCodeWriter()->doIf( implode(' || ', $this->expressions ) );
            // The executor will always end an if so we output a dummy if
            $executor->doIf('false');
        }
    }

    public function talesChainNothingKeyword(\PhpTal\Php\TalesChainExecutor $executor)
    {
        // end the chain
        $this->talesChainPart($executor, 'false', true);
        $executor->breakChain();
    }

    public function talesChainDefaultKeyword(\PhpTal\Php\TalesChainExecutor $executor)
    {
        throw new \PhpTal\Exception\ParserException('\'default\' keyword not allowed on conditional expressions',
                    $this->phpelement->getSourceFile(), $this->phpelement->getSourceLine());
    }
}
