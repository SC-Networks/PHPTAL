<?php
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

namespace PhpTal\Php\Attribute\I18N;

use PhpTal\Exception\ConfigurationException;
use PhpTal\Exception\PhpTalException;
use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;

/**
 * i18n:source
 *
 *  The i18n:source attribute specifies the language of the text to be
 *  translated. The default is "nothing", which means we don't provide
 *  this information to the translation services.
 *
 *
 * @package PHPTAL
 */
class Source extends Attribute
{
    /**
     * Called before element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws ConfigurationException
     * @throws PhpTalException
     */
    public function before(CodeWriter $codewriter): void
    {
        // ensure that a sources stack exists or create it
        $codewriter->doIf('!isset($_i18n_sources)');
        $codewriter->pushCode('$_i18n_sources = array()');
        $codewriter->doEnd();

        // push current source and use new one
        $codewriter->pushCode(
            '$_i18n_sources[] = ' . $codewriter->getTranslatorReference() . '->setSource(' . $codewriter->str($this->expression) . ')'
        );
    }

    /**
     * Called after element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @return void
     * @throws ConfigurationException
     */
    public function after(CodeWriter $codewriter): void
    {
        // restore source
        $code = $codewriter->getTranslatorReference() . '->setSource(array_pop($_i18n_sources))';
        $codewriter->pushCode($code);
    }
}
