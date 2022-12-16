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

class DummyObjectX
{
    private array $data;

    public function __construct()
    {
        $this->data = [];
    }

    public function __isset(string $var): bool
    {
        return array_key_exists($var, $this->data);
    }

    public function __get(string $var)
    {
        return $this->data[$var];
    }

    public function __set(string $var, $value)
    {
        $this->data[$var] = $value;
    }

    public function __call(string $method, $params): string
    {
        return '__call';
    }
}
