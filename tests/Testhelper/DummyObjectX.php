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

    /**
     * @var array
     */
    private $data;

    public function __contruct()
    {
        $this->data = [];
    }

    /**
     * @param string $var
     *
     * @return bool
     */
    public function __isset(string $var)
    {
        return array_key_exists($var, $this->data);
    }

    /**
     * @param string $var
     *
     * @return mixed
     */
    public function __get(string $var)
    {
        return $this->data[$var];
    }

    /**
     * @param string $var
     * @param mixed $value
     */
    public function __set(string $var, $value)
    {
        $this->data[$var] = $value;
    }

    /**
     * @param $method
     * @param $params
     *
     * @return string
     */
    public function __call($method, $params)
    {
        return '__call';
    }
}
