<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * Originally developed by Laurent Bedubourg and Kornel Lesiński
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @author   See contributors list @ github
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 * @link     https://github.com/SC-Networks/PHPTAL
 */

namespace Tests\Testhelper;

use PhpTal\TranslationServiceInterface;

class DummyTranslator implements TranslationServiceInterface
{
    /**
     * @var array
     */
    public $vars = [];

    /**
     * @var array
     */
    public $translations = [];

    /**
     * @var string|null
     */
    public $domain;

    public function setLanguage(...$langs): string
    {
        return '';
    }

    public function setEncoding(string $enc): void
    {
        // noop
    }

    public function useDomain(?string $domain): ?string
    {
        $this->domain = $domain;
        return null;
    }

    public function setVar(string $key, string $value): void
    {
        $this->vars[$key] = $value;
    }

    public function setTranslation(string $key, string $translation): void
    {
        $this->translations[$key] = $translation;
    }

    public function translate(?string $key, bool $escape = true): string
    {
        if (array_key_exists($key, $this->translations)) {
            $v = $this->translations[$key];
        } else {
            $v = $key;
        }

        if ($escape) {
            $v = htmlspecialchars($v ?? '');
        }

        while (preg_match('/\$\{(.*?)\}/sm', (string) $v, $m)) {
            [$src, $var] = $m;
            if (!isset($this->vars[$var])) {
                return "!*$var* is not defined!";
            }
            $v = str_replace($src, $this->vars[$var], (string) $v);
        }

        return $v;
    }
}
