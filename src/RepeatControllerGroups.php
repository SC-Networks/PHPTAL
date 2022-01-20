<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @author   Iván Montes <drslump@pollinimini.net>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal;

/**
 * Keeps track of variable contents when using grouping in a path (first/ and last/)
 *
 * @package PHPTAL
 */
class RepeatControllerGroups
{
    /**
     * @var array<string, string>
     */
    protected $dict = [];

    /**
     * @var array<string, string>
     */
    protected $cache = [];

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var array<string>
     */
    protected $vars = [];

    /**
     * @var string
     */
    protected $branch;


    public function __construct()
    {
        $this->dict = [];
        $this->reset();
    }

    /**
     * Resets the result caches. Use it to signal an iteration in the loop
     *
     */
    public function reset(): void
    {
        $this->cache = [];
    }

    /**
     * Checks if the data passed is the first one in a group
     *
     * @param mixed $data The data to evaluate
     *
     * @return Mixed    True if the first item in the group, false if not and
     *                  this same object if the path is not finished
     *
     * @todo cleanup this abomination of type abuse
     */
    public function first($data)
    {
        if (!is_array($data) && !is_object($data) && $data !== null) {
            if (!isset($this->cache['F'])) {
                $hash = md5($data);

                if (!isset($this->dict['F']) || $this->dict['F'] !== $hash) {
                    $this->dict['F'] = $hash;
                    $res = true;
                } else {
                    $res = false;
                }

                $this->cache['F'] = $res;
            }

            return $this->cache['F'];
        }

        $this->data = $data;
        $this->branch = 'F';
        $this->vars = [];
        return $this;
    }

    /**
     * Checks if the data passed is the last one in a group
     *
     * @param mixed $data The data to evaluate
     *
     * @return Mixed    True if the last item in the group, false if not and
     *                  this same object if the path is not finished
     *
     * @todo cleanup this abomination of type abuse
     */
    public function last($data)
    {
        if (!is_array($data) && !is_object($data) && $data !== null) {
            if (!isset($this->cache['L'])) {
                $hash = md5($data);

                if (!array_key_exists('L', $this->dict)) {
                    $this->dict['L'] = $hash;
                    $res = false;
                } elseif ($this->dict['L'] !== $hash) {
                    $this->dict['L'] = $hash;
                    $res = true;
                } else {
                    $res = false;
                }

                $this->cache['L'] = $res;
            }

            return $this->cache['L'];
        }

        $this->data = $data;
        $this->branch = 'L';
        $this->vars = [];
        return $this;
    }

    /**
     * Handles variable accesses for the tal path resolver
     *
     * @param string $var The variable name to check
     *
     * @return mixed    An object/array if the path is not over or a boolean
     *
     * @todo    replace the Context::path() with custom code
     * @throws Exception\VariableNotFoundException
     */
    public function __get(string $var)
    {
        // When the iterator item is empty we just let the tal
        // expression consume by continuously returning this
        // same object which should evaluate to true for 'last'
        if ($this->data === null) {
            return $this;
        }

        // Find the requested variable
        $value = Context::path($this->data, $var, true);

        // Check if it's an object or an array
        if (is_array($value) || is_object($value)) {
            // Move the context to the requested variable and return
            $this->data = $value;
            $this->addVarName($var);
            return $this;
        }

        // get a hash of the variable contents
        $hash = md5($value);

        // compute a path for the variable to use as dictionary key
        $path = $this->branch . $this->getVarPath() . $var;

        // If we don't know about this var store in the dictionary
        if (!isset($this->cache[$path])) {
            if (!isset($this->dict[$path])) {
                $this->dict[$path] = $hash;
                $res = $this->branch === 'F';
            } else {
                // Check if the value has changed
                if ($this->dict[$path] !== $hash) {
                    $this->dict[$path] = $hash;
                    $res = true;
                } else {
                    $res = false;
                }
            }

            $this->cache[$path] = $res;
        }

        return $this->cache[$path];
    }

    /**
     * Adds a variable name to the current path of variables
     *
     * @param string $varname The variable name to store as a path part
     */
    protected function addVarName(string $varname): void
    {
        $this->vars[] = $varname;
    }

    /**
     * Returns the current variable path separated by a slash
     *
     * @return String  The current variable path
     */
    protected function getVarPath(): string
    {
        return implode('/', $this->vars) . '/';
    }
}
