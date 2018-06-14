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

namespace PhpTal;

/**
 * \PhpTal\TranslationService gettext implementation.
 *
 * Because gettext is the most common translation library in use, this
 * implementation is shipped with the PHPTAL library.
 *
 * Please refer to the PHPTAL documentation for usage examples.
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class GetTextTranslator implements TranslationServiceInterface
{
    /**
     * @var array
     */
    private $vars = [];

    /**
     * @var string
     */
    private $currentDomain;

    /**
     * @var string
     */
    private $encoding = 'UTF-8';

    /**
     * GetTextTranslator constructor.
     * @throws Exception\ConfigurationException
     */
    public function __construct()
    {
        if (!function_exists('gettext')) {
            throw new Exception\ConfigurationException('Gettext not installed');
        }
    }

    /**
     * set encoding that is used by template and is expected from gettext
     * the default is UTF-8
     *
     * @param string $enc encoding name
     */
    public function setEncoding(string $enc): void
    {
        $this->encoding = $enc;
    }

    /**
     * It expects locale names as arguments.
     * Choses first one that works.
     *
     * setLanguage("en_US.utf8","en_US","en_GB","en")
     *
     * @param array $langs
     * @return string - chosen language
     * @throws Exception\ConfigurationException
     */
    public function setLanguage(...$langs): string
    {

        $langCode = $this->trySettingLanguages(LC_ALL, $langs);
        if ($langCode) {
            return $langCode;
        }

        if (defined('LC_MESSAGES')) {
            $langCode = $this->trySettingLanguages(LC_MESSAGES, $langs);
            if ($langCode) {
                return $langCode;
            }
        }

        throw new Exception\ConfigurationException(
            'Language(s) code(s) "' . implode(', ', $langs) . '" not supported by your system'
        );
    }

    /**
     * @param int $category
     * @param array $langs
     *
     * @return string
     */
    private function trySettingLanguages(int $category, array $langs): ?string
    {
        foreach ($langs as $langCode) {
            putenv("LANG=$langCode");
            putenv("LC_ALL=$langCode");
            putenv("LANGUAGE=$langCode");
            if (setlocale($category, $langCode)) {
                return $langCode;
            }
        }
        return null;
    }

    /**
     * Adds translation domain (usually it's the same as name of .po file [without extension])
     *
     * Encoding must be set before calling addDomain!
     *
     * @param string $domain
     * @param string $path
     */
    public function addDomain(string $domain, ?string $path = null): void
    {
        bindtextdomain($domain, $path ?? './locale/');
        if ($this->encoding) {
            bind_textdomain_codeset($domain, $this->encoding);
        }
        $this->useDomain($domain);
    }

    /**
     * Switches to one of the domains previously set via addDomain()
     *
     * @param string $domain name of translation domain to be used.
     *
     * @return string - old domain
     */
    public function useDomain(string $domain): ?string
    {
        $old = $this->currentDomain;
        $this->currentDomain = $domain;
        textdomain($domain);
        return $old;
    }

    /**
     * used by generated PHP code. Don't use directly.
     *
     * @param string $key
     * @param mixed $value
     */
    public function setVar(string $key, $value): void
    {
        $this->vars[$key] = $value;
    }

    /**
     * translate given key.
     *
     * @param string $key
     * @param bool $htmlencode if true, output will be HTML-escaped.
     *
     * @return string
     * @throws Exception\VariableNotFoundException
     */
    public function translate(string $key, bool $htmlencode): string
    {

        $value = gettext($key);

        if ($htmlencode) {
            $value = htmlspecialchars($value, ENT_QUOTES, $this->encoding);
        }
        while (preg_match('/\${(.*?)\}/sm', $value, $m)) {
            list($src, $var) = $m;
            if (!array_key_exists($var, $this->vars)) {
                throw new Exception\VariableNotFoundException(
                    'Interpolation error. Translation uses ${' . $var . '}, which is not defined in the template (via i18n:name)'
                );
            }
            $value = str_replace($src, $this->vars[$var], $value);
        }
        return $value;
    }
}
