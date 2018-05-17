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

namespace PhpTal\Php\Attribute\I18N;

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
class Source extends \PhpTal\Php\Attribute
{
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        // ensure that a sources stack exists or create it
        $codewriter->doIf('!isset($_i18n_sources)');
        $codewriter->pushCode('$_i18n_sources = array()');
        $codewriter->end();

        // push current source and use new one
        $codewriter->pushCode('$_i18n_sources[] = ' . $codewriter->getTranslatorReference(). '->setSource('.$codewriter->str($this->expression).')');
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
        // restore source
        $code = $codewriter->getTranslatorReference().'->setSource(array_pop($_i18n_sources))';
        $codewriter->pushCode($code);
    }
}
