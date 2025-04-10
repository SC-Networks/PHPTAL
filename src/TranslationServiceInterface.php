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

namespace PhpTal;

/**
 * @package PHPTAL
 */
interface TranslationServiceInterface
{
    /**
     * Set the target language for translations.
     *
     * When set to '' no translation will be done.
     *
     * You can specify a list of possible language for exemple :
     *
     * setLanguage('fr_FR', 'fr_FR@euro')
     *
     * @param string $langs
     *
     * @return string - chosen language
     */
    public function setLanguage(...$langs): string;

    /**
     * PHPTAL will inform translation service what encoding page uses.
     * Output of translate() must be in this encoding.
     */
    public function setEncoding(string $encoding): void;

    /**
     * Set the domain to use for translations (if different parts of application are translated in different files.
     * This is not for language selection).
     *
     *
     * @return string
     */
    public function useDomain(string $domain): ?string;

    /**
     * Set XHTML-escaped value of a variable used in translation key.
     *
     * You should use it to replace all ${key}s with values in translated strings.
     *
     * @param string $key - name of the variable
     * @param string $value_escaped - XHTML markup
     */
    public function setVar(string $key, string $value_escaped): void;

    /**
     * Translate a gettext key and interpolate variables.
     *
     * @param string $key - translation key, e.g. "hello ${username}!"
     * @param bool $htmlescape - if true, you should HTML-escape translated string.
     *                             You should never HTML-escape interpolated variables.
     */
    public function translate(string $key, bool $htmlescape): string;
}
