<?php
declare(strict_types=1);

namespace PhpTal;

use ReflectionException;

/**
 * TalesRegistryInterface
 *
 * @package PHPTAL
 *
 * @phpstan-type CallbackReturnType callable(string, bool):mixed|callable-string|array{0: class-string, 1: string}
 */
interface TalesRegistryInterface
{
    /**
     * get callback for the prefix
     *
     *
     * @return null|CallbackReturnType
     */
    public static function getCallback(string $prefix): null|array|string|callable;

    /**
     * true if given prefix is taken
     *
     *
     */
    public static function isRegistered(string $prefix): bool;

    /**
     *
     * Expects an either a function name or an array of class and method as
     * callback.
     *
     * @param string|callable-string|callable(?string, bool):mixed|array{0: callable-string, 1: string} $callback
     * @param bool $is_fallback if true, method will be used as last resort (if there's no phptal_tales_foo)
     *
     * @throws Exception\ConfigurationException
     * @throws ReflectionException
     */
    public static function registerPrefix(string $prefix, $callback, ?bool $is_fallback = null): void;

    /**
     * Unregisters a expression modifier
     *
     *
     * @throws Exception\ConfigurationException
     */
    public static function unregisterPrefix(string $prefix): void;
}
