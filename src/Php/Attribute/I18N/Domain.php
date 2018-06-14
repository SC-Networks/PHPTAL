<?php
declare(strict_types=1);

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

namespace PhpTal\Php\Attribute\I18N;

use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;

/**
 * i18n:domain
 *
 * The i18n:domain attribute is used to specify the domain to be used to get
 * the translation. If not specified, the translation services will use a
 * default domain. The value of the attribute is used directly; it is not
 * a TALES expression.
 *
 * @package PHPTAL
 */
class Domain extends Attribute
{
    /**
     * Called before element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws \PhpTal\Exception\ConfigurationException
     * @throws \PhpTal\Exception\ParserException
     * @throws \PhpTal\Exception\PhpTalException
     * @throws \PhpTal\Exception\UnknownModifierException
     * @throws \ReflectionException
     */
    public function before(CodeWriter $codewriter): void
    {
        // ensure a domain stack exists or create it
        $codewriter->doIf('!isset($_i18n_domains)');
        $codewriter->pushCode('$_i18n_domains = array()');
        $codewriter->doEnd('if');

        $expression = $codewriter->interpolateTalesVarsInString($this->expression);

        // push current domain and use new domain
        $code = '$_i18n_domains[] = ' . $codewriter->getTranslatorReference() . '->useDomain(' . $expression . ')';
        $codewriter->pushCode($code);
    }

    /**
     * Called after element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws \PhpTal\Exception\ConfigurationException
     */
    public function after(CodeWriter $codewriter): void
    {
        // restore domain
        $code = $codewriter->getTranslatorReference() . '->useDomain(array_pop($_i18n_domains))';
        $codewriter->pushCode($code);
    }
}
