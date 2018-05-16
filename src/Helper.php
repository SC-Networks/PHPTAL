<?php

namespace PhpTal;

/**
 * Class Helper
 * @package PhpTal
 */
class Helper
{

    /**
     * helper function for chained expressions
     *
     * @param mixed $var value to check
     * @return bool
     * @access private
     */
    public static function phptal_isempty($var)
    {
        return $var === null || $var === false || $var === ''
            || ((is_array($var) || $var instanceof \Countable) && count($var) === 0);
    }


    /**
     * helper function for conditional expressions
     *
     * @param mixed $var value to check
     * @return bool
     * @access private
     */
    public static function phptal_true($var)
    {
        $var = static::phptal_unravel_closure($var);
        return $var && (!$var instanceof \Countable || count($var));
    }


    /**
     * convert to string and html-escape given value (of any type)
     *
     * @access private
     */
    public static function phptal_escape($var, $encoding)
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
    public static function phptal_tostring($var)
    {
        if (is_string($var)) {
            return $var;
        }

        if (is_bool($var)) {
            return (int)$var;
        } elseif (is_array($var)) {
            return implode(', ', array_map([__CLASS__, 'phptal_tostring'], $var));
        } elseif ($var instanceof \SimpleXMLElement) {
            /* There is no sane way to tell apart element and attribute nodes
               in SimpleXML, so here's a guess that if something has no attributes
               or children, and doesn't output <, then it's an attribute */

            $xml = $var->asXML();
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
     */
    public static function phptal_unravel_closure($var)
    {
        while (is_object($var) && is_callable($var)) {
            $var = call_user_func($var);
        }
        return $var;
    }
}
