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


class DummyTranslator implements TranslationServiceInterface
{
    public $vars = array();
    public $translations = array();
    public $domain;

    public function setLanguage(...$langs): string
    {
        return '';
    }

    public function setEncoding(string $enc): void {}

    public function useDomain(?string $domain): ?string
    {
        $this->domain = $domain;
        return null;
    }

    public function setVar(string $key, $value): void
    {
        $this->vars[$key] = $value;
    }

    public function setTranslation($key, $translation)
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

        if ($escape) $v = htmlspecialchars($v ?? '');

        while (preg_match('/\$\{(.*?)\}/sm', $v, $m)) {
            list($src, $var) = $m;
            if (!isset($this->vars[$var])) return "!*$var* is not defined!";
            $v = str_replace($src, $this->vars[$var], $v);
        }


        return $v;
    }
}
