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
 * @link     http://phptal.org/
 */

namespace PhpTal;

use PhpTal\Php\TalesInternal;

/**
 * Global registry of TALES expression modifiers
 *
 * @package PHPTAL
 */
class TalesRegistry
{
    /**
     * @var TalesRegistry
     */
    private static $instance;

    /**
     * {callback, bool is_fallback}
     * @var array
     */
    private $callbacks = [];

    /**
     * This is a singleton
     *
     * @return \PhpTal\TalesRegistry
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new TalesRegistry();
        }

        return self::$instance;
    }

    protected function __construct()
    {
        $this->registerPrefix('not', [TalesInternal::class, 'not']);
        $this->registerPrefix('path', [TalesInternal::class, 'path']);
        $this->registerPrefix('string', [TalesInternal::class, 'string']);
        $this->registerPrefix('php', [TalesInternal::class, 'php']);
        $this->registerPrefix('exists', [TalesInternal::class, 'exists']);
        $this->registerPrefix('number', [TalesInternal::class, 'number']);
        $this->registerPrefix('true', [TalesInternal::class, 'true']);

        // these are added as fallbacks
        $this->registerPrefix('json', [TalesInternal::class, 'json'], true);
        $this->registerPrefix('urlencode', [TalesInternal::class, 'urlencode'], true);
    }

    /**
     * Unregisters a expression modifier
     *
     * @param string $prefix
     *
     * @throws Exception\ConfigurationException
     */
    public function unregisterPrefix($prefix)
    {
        if (!$this->isRegistered($prefix)) {
            throw new Exception\ConfigurationException("Expression modifier '$prefix' is not registered");
        }

        unset($this->callbacks[$prefix]);
    }

    /**
     *
     * Expects an either a function name or an array of class and method as
     * callback.
     *
     * @param string $prefix
     * @param mixed $callback
     * @param bool $is_fallback if true, method will be used as last resort (if there's no phptal_tales_foo)
     * @throws Exception\ConfigurationException
     * @throws \ReflectionException
     */
    public function registerPrefix($prefix, $callback, $is_fallback = false)
    {
        if ($this->isRegistered($prefix) && !$this->callbacks[$prefix]['is_fallback']) {
            if ($is_fallback) {
                return; // simply ignored
            }
            throw new Exception\ConfigurationException("Expression modifier '$prefix' is already registered");
        }

        // Check if valid callback

        if (is_array($callback)) {
            $class = new \ReflectionClass($callback[0]);

            if (!$class->isSubclassOf(TalesInterface::class)) {
                throw new Exception\ConfigurationException(
                    'The class you want to register does not implement "\PhpTal\Tales".'
                );
            }

            $method = new \ReflectionMethod($callback[0], $callback[1]);

            if (!$method->isStatic()) {
                throw new Exception\ConfigurationException('The method you want to register is not static.');
            }

            // maybe we want to check the parameters the method takes

        } elseif (!function_exists($callback)) {
            throw new Exception\ConfigurationException('The function you are trying to register does not exist.');
        }

        $this->callbacks[$prefix] = ['callback' => $callback, 'is_fallback' => $is_fallback];
    }

    /**
     * true if given prefix is taken
     *
     * @param string $prefix
     *
     * @return bool
     */
    private function isRegistered($prefix)
    {
        return array_key_exists($prefix, $this->callbacks);
    }

    /**
     * @param string $typePrefix
     *
     * @return array|null|string
     * @throws Exception\UnknownModifierException
     * @throws \ReflectionException
     */
    private function findUnregisteredCallback($typePrefix)
    {
        // class method
        if (strpos($typePrefix, '.')) {
            $classCallback = explode('.', $typePrefix, 2);
            $callbackName = null;
            if (!is_callable($classCallback, false, $callbackName)) {
                throw new Exception\UnknownModifierException(
                    "Unknown phptal modifier $typePrefix. Function $callbackName does not exists or is not statically callable",
                    $typePrefix
                );
            }
            $ref = new \ReflectionClass($classCallback[0]);
            if (!$ref->implementsInterface(TalesInterface::class)) {
                throw new Exception\UnknownModifierException(
                    "Unable to use phptal modifier $typePrefix as the class $callbackName does not implement the \PhpTal\Tales interface",
                    $typePrefix
                );
            }
            return $classCallback;
        }

        // check if it is implemented via code-generating function
        $func = 'phptal_tales_' . str_replace('-', '_', $typePrefix);
        if (function_exists($func)) {
            return $func;
        }

        return null;
    }

    /**
     * get callback for the prefix
     *
     * @param $prefix
     *
     * @return callback or NULL
     * @throws Exception\UnknownModifierException
     * @throws \ReflectionException
     */
    public function getCallback($prefix)
    {
        if ($this->isRegistered($prefix) && !$this->callbacks[$prefix]['is_fallback']) {
            return $this->callbacks[$prefix]['callback'];
        }

        $callback = $this->findUnregisteredCallback($prefix);
        if ($callback) {
            return $callback;
        }

        if ($this->isRegistered($prefix)) {
            return $this->callbacks[$prefix]['callback'];
        }

        return null;
    }
}
