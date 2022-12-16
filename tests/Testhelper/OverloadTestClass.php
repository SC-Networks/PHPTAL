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

class OverloadTestClass
{
    /**
     * @var array
     */
    public $vars = ['foo' => 'bar', 'baz' => 'biz'];

    public function __set(string $name, mixed $value)
    {
        $this->vars[$name] = $value;
    }

    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if (array_key_exists($name, $this->vars)) {
            return $this->vars[$name];
        }
        return null;
    }

    /**
     * @return bool
     */
    public function __isset(string $key)
    {
        return isset($this->$key) || array_key_exists($key, $this->vars);
    }

    /**
     *
     * @return string
     */
    public function __call(string $func, array $args)
    {
        return "$func()=" . implode(',', $args);
    }
}
