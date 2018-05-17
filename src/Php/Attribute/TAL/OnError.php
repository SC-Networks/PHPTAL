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
 *      argument ::= (['text'] | 'structure') expression
 *
 * Example:
 *
 *      <p tal:on-error="string: Error! This paragraph is buggy!">
 *      My name is <span tal:replace="here/SlimShady" />.<br />
 *      (My login name is
 *      <b tal:on-error="string: Username is not defined!"
 *         tal:content="user">Unknown</b>)
 *      </p>
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class OnError extends \PhpTal\Php\Attribute
{
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $codewriter->doTry();
        $codewriter->pushCode('ob_start()');
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
        $var = $codewriter->createTempVariable();

        $codewriter->pushCode('ob_end_flush()');
        $codewriter->doCatch('Exception '.$var);
        $codewriter->pushCode('$tpl->addError('.$var.')');
        $codewriter->pushCode('ob_end_clean()');

        $expression = $this->extractEchoType($this->expression);

        $code = $codewriter->evaluateExpression($expression);
        switch ($code) {
            case \PhpTal\Php\TalesInternal::NOTHING_KEYWORD:
                break;

            case \PhpTal\Php\TalesInternal::DEFAULT_KEYWORD:
                $codewriter->pushHTML('<pre class="phptalError">');
                $codewriter->doEcho($var);
                $codewriter->pushHTML('</pre>');
                break;

            default:
                $this->doEchoAttribute($codewriter, $code);
                break;
        }
        $codewriter->doEnd('catch');

        $codewriter->recycleTempVariable($var);
    }
}
