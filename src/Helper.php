<?php

namespace PhpTal;

use Countable;
use SimpleXMLElement;

/**
 * Class Helper
 *
 * @package PhpTal
 */
class Helper
{
    /**
     * helper function for chained expressions
     *
     * @param mixed $var value to check
     * @access private
     */
    public static function phptal_isempty(mixed $var): bool
    {
        return in_array($var, [null, false, ''], true)
            || ((is_countable($var)) && count($var) === 0);
    }

    /**
     * helper function for conditional expressions
     *
     * @param mixed $var value to check
     * @access private
     */
    public static function phptal_true(mixed $var): bool
    {
        $var = static::phptal_unravel_closure($var);
        return $var && (!$var instanceof Countable || count($var));
    }

    /**
     * convert to string and html-escape given value (of any type)
     *
     * @access private
     *
     * @param string $encoding
     */
    public static function phptal_escape(mixed $var, $encoding): string
    {
        if (is_string($var)) {
            return htmlspecialchars($var, ENT_QUOTES, $encoding);
        }
        return htmlspecialchars(static::phptal_tostring($var), ENT_QUOTES, $encoding);
    }

    /**
     * convert anything to string
     *
     * @access private
     */
    public static function phptal_tostring(mixed $var): string
    {
        if (is_string($var)) {
            return $var;
        }

        if (is_bool($var)) {
            return (string)(int)$var;
        }
        if (is_array($var)) {
            return implode(', ', array_map(self::phptal_tostring(...), $var));
        }
        if ($var instanceof SimpleXMLElement) {
            /* There is no sane way to tell apart element and attribute nodes
               in SimpleXML, so here's a guess that if something has no attributes
               or children, and doesn't output <, then it's an attribute */

            $xml = (string) $var->asXML();
            if ($xml[0] === '<' || $var->attributes() || $var->children()) {
                return $xml;
            }
        }
        return (string) static::phptal_unravel_closure($var);
    }

    /**
     * unravel the provided expression if it is a closure
     *
     * This will call the base expression and its result
     * as long as it is a Closure.  Once the base (non-Closure)
     * value is found it is returned.
     *
     * This function has no effect on non-Closure expressions
     *
     * @return mixed
     */
    public static function phptal_unravel_closure(mixed $var)
    {
        while (is_object($var) && is_callable($var)) {
            $var = $var();
        }
        return $var;
    }
}
