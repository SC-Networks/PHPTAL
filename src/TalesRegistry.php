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

namespace PhpTal;

/**
 * Global registry of TALES expression modifiers
 *
 * @package PHPTAL
 */
class TalesRegistry
{
    private static $instance;

    /**
     * This is a singleton
     *
     * @return \PhpTal\TalesRegistry
     */
    static public function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new TalesRegistry();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $this->registerPrefix('not', array('\PhpTal\Php\TalesInternal', 'not'));
        $this->registerPrefix('path', array('\PhpTal\Php\TalesInternal', 'path'));
        $this->registerPrefix('string', array('\PhpTal\Php\TalesInternal', 'string'));
        $this->registerPrefix('php', array('\PhpTal\Php\TalesInternal', 'php'));
        $this->registerPrefix('phptal-internal-php-block', array('\PhpTal\Php\TalesInternal', 'phptal_internal_php_block'));
        $this->registerPrefix('exists', array('\PhpTal\Php\TalesInternal', 'exists'));
        $this->registerPrefix('number', array('\PhpTal\Php\TalesInternal', 'number'));
        $this->registerPrefix('true', array('\PhpTal\Php\TalesInternal', 'true'));

        // these are added as fallbacks
        $this->registerPrefix('json', array('\PhpTal\Php\TalesInternal', 'json'), true);
        $this->registerPrefix('urlencode', array('\PhpTal\Php\TalesInternal', 'urlencode'), true);
    }

    /**
     * Unregisters a expression modifier
     *
     * @param string $prefix
     *
     * @throws \PhpTal\Exception\ConfigurationException
     */
    public function unregisterPrefix($prefix)
    {
        if (!$this->isRegistered($prefix)) {
            throw new \PhpTal\Exception\ConfigurationException("Expression modifier '$prefix' is not registered");
        }

        unset($this->_callbacks[$prefix]);
    }

    /**
     *
     * Expects an either a function name or an array of class and method as
     * callback.
     *
     * @param string $prefix
     * @param mixed $callback
     * @param bool $is_fallback if true, method will be used as last resort (if there's no phptal_tales_foo)
     */
    public function registerPrefix($prefix, $callback, $is_fallback = false)
    {
        if ($this->isRegistered($prefix) && !$this->_callbacks[$prefix]['is_fallback']) {
            if ($is_fallback) {
                return; // simply ignored
            }
            throw new \PhpTal\Exception\ConfigurationException("Expression modifier '$prefix' is already registered");
        }

        // Check if valid callback

        if (is_array($callback)) {

            $class = new \ReflectionClass($callback[0]);

            if (!$class->isSubclassOf(Tales::class)) {
                throw new \PhpTal\Exception\ConfigurationException('The class you want to register does not implement "\PhpTal\Tales".');
            }

            $method = new \ReflectionMethod($callback[0], $callback[1]);

            if (!$method->isStatic()) {
                throw new \PhpTal\Exception\ConfigurationException('The method you want to register is not static.');
            }

            // maybe we want to check the parameters the method takes

        } else {
            if (!function_exists($callback)) {
                throw new \PhpTal\Exception\ConfigurationException('The function you are trying to register does not exist.');
            }
        }

        $this->_callbacks[$prefix] = array('callback'=>$callback, 'is_fallback'=>$is_fallback);
    }

    /**
     * true if given prefix is taken
     */
    public function isRegistered($prefix)
    {
        if (array_key_exists($prefix, $this->_callbacks)) {
            return true;
        }
    }

    private function findUnregisteredCallback($typePrefix)
    {
        // class method
        if (strpos($typePrefix, '.')) {
            $classCallback = explode('.', $typePrefix, 2);
            $callbackName  = null;
            if (!is_callable($classCallback, false, $callbackName)) {
                throw new \PhpTal\Exception\UnknownModifierException("Unknown phptal modifier $typePrefix. Function $callbackName does not exists or is not statically callable", $typePrefix);
            }
            $ref = new \ReflectionClass($classCallback[0]);
            if (!$ref->implementsInterface('\PhpTal\Tales')) {
                throw new \PhpTal\Exception\UnknownModifierException("Unable to use phptal modifier $typePrefix as the class $callbackName does not implement the \PhpTal\Tales interface", $typePrefix);
            }
            return $classCallback;
        }

        // check if it is implemented via code-generating function
        $func = 'phptal_tales_'.str_replace('-', '_', $typePrefix);
        if (function_exists($func)) {
            return $func;
        }

//        // The following code is automatically modified in version for PHP 5.3
//        $func = 'PHPTALNAMESPACE\\phptal_tales_'.str_replace('-', '_', $typePrefix);
//        if (function_exists($func)) {
//            return $func;
//        }

        return null;
    }

    /**
     * get callback for the prefix
     *
     * @return callback or NULL
     */
    public function getCallback($prefix)
    {
        if ($this->isRegistered($prefix) && !$this->_callbacks[$prefix]['is_fallback']) {
            return $this->_callbacks[$prefix]['callback'];
        }

        if ($callback = $this->findUnregisteredCallback($prefix)) {
            return $callback;
        }

        if ($this->isRegistered($prefix)) {
            return $this->_callbacks[$prefix]['callback'];
        }

        return null;
    }

    /**
     * {callback, bool is_fallback}
     */
    private $_callbacks = array();
}
