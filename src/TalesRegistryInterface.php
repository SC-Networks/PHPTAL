<?php
declare(strict_types=1);

namespace PhpTal;

/**
 * TalesRegistryInterface
 *
 * @package PHPTAL
 */
interface TalesRegistryInterface
{


    /**
     * get callback for the prefix
     *
     * @param string $prefix
     *
     * @return callback or NULL
     */
    public static function getCallback(string $prefix): ?callable;

    /**
     * true if given prefix is taken
     *
     * @param string $prefix
     *
     * @return bool
     */
    public static function isRegistered(string $prefix): bool;

    /**
     *
     * Expects an either a function name or an array of class and method as
     * callback.
     *
     * @param string $prefix
     * @param mixed $callback
     * @param bool $is_fallback if true, method will be used as last resort (if there's no phptal_tales_foo)
     *
     * @throws Exception\ConfigurationException
     * @throws \ReflectionException
     */
    public static function registerPrefix(string $prefix, $callback, ?bool $is_fallback = null): void;

    /**
     * Unregisters a expression modifier
     *
     * @param string $prefix
     *
     * @throws Exception\ConfigurationException
     */
    public static function unregisterPrefix(string $prefix): void;
}
