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
use PhpTal\Php\TalesInternal;
use ReflectionException;

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
class OnError extends Attribute
{
    /**
     * Called before element printing.
     *
     *
     */
    public function before(CodeWriter $codewriter): void
    {
        $codewriter->doTry();
        $codewriter->pushCode('ob_start()');
    }

    /**
     * Called after element printing.
     *
     *
     * @throws ParserException
     * @throws PhpNotAllowedException
     * @throws PhpTalException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    public function after(CodeWriter $codewriter): void
    {
        $var = $codewriter->createTempVariable();

        $codewriter->pushCode('ob_end_flush()');
        $codewriter->doCatch('Exception '.$var);
        $codewriter->pushCode('$tpl->addError('.$var.')');
        $codewriter->pushCode('ob_end_clean()');

        $expression = $this->extractEchoType($this->expression);

        $code = $codewriter->evaluateExpression($expression);
        switch ($code) {
            case TalesInternal::NOTHING_KEYWORD:
                break;

            case TalesInternal::DEFAULT_KEYWORD:
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
