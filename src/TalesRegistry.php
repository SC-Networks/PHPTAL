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

use PhpTal\Php\TalesInternal;

/**
 * Global registry of TALES expression modifiers
 *
 * @package PHPTAL
 */
final class TalesRegistry implements TalesRegistryInterface
{

    /**
     * {callback, bool is_fallback}
     * @var array
     */
    private static $callbacks = [
        'not' => ['callback' => [TalesInternal::class, 'not'], 'is_fallback' => false],
        'path' => ['callback' => [TalesInternal::class, 'path'], 'is_fallback' => false],
        'string' => ['callback' => [TalesInternal::class, 'string'], 'is_fallback' => false],
        'php' => ['callback' => [TalesInternal::class, 'php'], 'is_fallback' => false],
        'exists' => ['callback' => [TalesInternal::class, 'exists'], 'is_fallback' => false],
        'number' => ['callback' => [TalesInternal::class, 'number'], 'is_fallback' => false],
        'true' => ['callback' => [TalesInternal::class, 'true'], 'is_fallback' => false],
        'json'=> ['callback' => [TalesInternal::class, 'json'], 'is_fallback' => true],
        'urlencode'=> ['callback' => [TalesInternal::class, 'urlencode'], 'is_fallback' => true],
    ];

    /**
     * Unregisters a expression modifier
     *
     * @param string $prefix
     *
     * @throws Exception\ConfigurationException
     */
    public static function unregisterPrefix(string $prefix): void
    {
        if (!static::isRegistered($prefix)) {
            throw new Exception\ConfigurationException("Expression modifier '$prefix' is not registered");
        }

        unset(static::$callbacks[$prefix]);
    }

    /**
     *
     * Expects either a function name or an array of class and method or a closure as callback.
     * A closure *must* return a string enclosed in double quotes.
     *
     * @param string $prefix
     * @param mixed $callback
     * @param bool $is_fallback if true, method will be used as last resort (if there's no phptal_tales_foo)
     *
     * @throws Exception\ConfigurationException
     * @throws \ReflectionException
     */
    public static function registerPrefix(string $prefix, $callback, ?bool $is_fallback = null): void
    {
        if (static::isRegistered($prefix) && !static::$callbacks[$prefix]['is_fallback']) {
            if ($is_fallback === true) {
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
        } elseif (is_callable($callback)) {
            // do nothing
        } elseif (!function_exists($callback)) {
            throw new Exception\ConfigurationException('The function you are trying to register does not exist.');
        }

        static::$callbacks[$prefix] = ['callback' => $callback, 'is_fallback' => $is_fallback ?? false];
    }

    /**
     * true if given prefix is taken
     *
     * @param string $prefix
     *
     * @return bool
     */
    public static function isRegistered(string $prefix): bool
    {
        return array_key_exists($prefix, static::$callbacks);
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
    public static function getCallback(string $prefix): ?callable
    {
        if (!static::isRegistered($prefix)) {
            return null;
        }

        return static::$callbacks[$prefix]['callback'];
    }
}
