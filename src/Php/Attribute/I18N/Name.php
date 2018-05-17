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

namespace PhpTal\Php\Attribute\I18N;

/** i18n:name
 *
 * Name the content of the current element for use in interpolation within
 * translated content. This allows a replaceable component in content to be
 * re-ordered by translation. For example:
 *
 * <span i18n:translate=''>
 *   <span tal:replace='here/name' i18n:name='name' /> was born in
 *   <span tal:replace='here/country_of_birth' i18n:name='country' />.
 * </span>
 *
 * would cause this text to be passed to the translation service:
 *
 *     "${name} was born in ${country}."
 *
 *
 * @package PHPTAL
 */
class Name extends \PhpTal\Php\Attribute
{
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $codewriter->pushCode('ob_start()');
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
        $codewriter->pushCode($codewriter->getTranslatorReference().'->setVar('.$codewriter->str($this->expression).', ob_get_clean())');
    }
}
