<?php
namespace PhpTal;

use PhpTal\Php\TalesInternal;

/**
 * TalesRegistryInterface
 *
 * @package PHPTAL
 */
interface TalesRegistryInterface
{

    /**
     * This is a singleton
     *
     * @return TalesRegistryInterface
     */
    public static function getInstance();

    /**
     * Unregisters a expression modifier
     *
     * @param string $prefix
     */
    public function unregisterPrefix($prefix);

    /**
     *
     * Expects an either a function name or an array of class and method as
     * callback.
     *
     * @param string $prefix
     * @param mixed $callback
     * @param bool $is_fallback if true, method will be used as last resort (if there's no phptal_tales_foo)
     */
    public function registerPrefix($prefix, $callback, $is_fallback = false);

    /**
     * true if given prefix is taken
     *
     * @param string $prefix
     *
     * @return bool
     */
    public function isRegistered($prefix);

    /**
     * get callback for the prefix
     *
     * @param $prefix
     *
     * @return callback or NULL
     */
    public function getCallback($prefix);
}
