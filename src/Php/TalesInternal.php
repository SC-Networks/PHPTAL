<?php
/**
 * PHPTAL templating engine
 *
 * PHP Version 5
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Moritz Bechler <mbechler@eenterphace.org>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal\Php;

use PhpTal\Exception\ParserException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\TalesRegistry;

/**
 * TALES Specification 1.3
 *
 *      Expression  ::= [type_prefix ':'] String
 *      type_prefix ::= Name
 *
 * Examples:
 *
 *      a/b/c
 *      path:a/b/c
 *      nothing
 *      path:nothing
 *      python: 1 + 2
 *      string:Hello, ${username}
 *
 *
 * Builtin Names in Page Templates (for PHPTAL)
 *
 *      * nothing - special singleton value used by TAL to represent a
 *        non-value (e.g. void, None, Nil, NULL).
 *
 *      * default - special singleton value used by TAL to specify that
 *        existing text should not be replaced.
 *
 *      * repeat - the repeat variables (see RepeatVariable).
 *
 *
 */

/**
 * @package PHPTAL
 */
class TalesInternal implements \PhpTal\TalesInterface
{
    const DEFAULT_KEYWORD = 'new \PhpTal\DefaultKeyword';
    const NOTHING_KEYWORD = 'new \PhpTal\NothingKeyword';

    /**
     * @param string $src
     * @param bool $nothrow
     *
     * @return string
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function true($src, $nothrow)
    {
        return '\PhpTal\Helper::phptal_true(' . self::compileToPHPExpression($src, true) . ')';
    }

    /**
     * not:
     *
     *      not: Expression
     *
     * evaluate the expression string (recursively) as a full expression,
     * and returns the boolean negation of its value
     *
     * return boolean based on the following rules:
     *
     *     1. integer 0 is false
     *     2. integer > 0 is true
     *     3. an empty string or other sequence is false
     *     4. a non-empty string or other sequence is true
     *     5. a non-value (e.g. void, None, Nil, NULL, etc) is false
     *     6. all other values are implementation-dependent.
     *
     * Examples:
     *
     *      not: exists: foo/bar/baz
     *      not: php: object.hasChildren()
     *      not: string:${foo}
     *      not: foo/bar/booleancomparable
     *
     * @param string $expression
     * @param bool $nothrow
     *
     * @return string
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function not($expression, $nothrow)
    {
        return '!\PhpTal\Helper::phptal_true(' . self::compileToPHPExpression($expression, $nothrow) . ')';
    }


    /**
     * path:
     *
     *      PathExpr  ::= Path [ '|' Path ]*
     *      Path      ::= variable [ '/' URL_Segment ]*
     *      variable  ::= Name
     *
     * Examples:
     *
     *      path: username
     *      path: user/name
     *      path: object/method/10/method/member
     *      path: object/${dynamicmembername}/method
     *      path: maybethis | path: maybethat | path: default
     *
     * PHPTAL:
     *
     * 'default' may lead to some 'difficult' attributes implementation
     *
     * For example, the tal:content will have to insert php code like:
     *
     * if (isset($ctx->maybethis)) {
     *     echo $ctx->maybethis;
     * }
     * elseif (isset($ctx->maybethat) {
     *     echo $ctx->maybethat;
     * }
     * else {
     *     // process default tag content
     * }
     *
     * @param string $expression
     * @param bool $nothrow
     *
     * @return array|string
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function path($expression, $nothrow = false)
    {
        $expression = trim($expression);
        if ($expression === 'default') {
            return self::DEFAULT_KEYWORD;
        }
        if ($expression === 'nothing') {
            return self::NOTHING_KEYWORD;
        }
        if ($expression === '') {
            return self::NOTHING_KEYWORD;
        }

        $string = null;

        // split OR expressions terminated by a string
        if (preg_match('/^(.*?)\s*\|\s*?(string:.*)$/sm', $expression, $m)) {
            list(, $expression, $string) = $m;
        } // split OR expressions terminated by a 'fast' string
        elseif (preg_match('/^(.*?)\s*\|\s*\'((?:[^\'\\\\]|\\\\.)*)\'\s*$/sm', $expression, $m)) {
            list(, $expression, $string) = $m;
            $string = 'string:' . stripslashes($string);
        }

        // split OR expressions
        $exps = preg_split('/\s*\|\s*/sm', $expression);

        // if (many expressions) or (expressions or terminating string) found then
        // generate the array of sub expressions and return it.
        if (count($exps) > 1 || $string !== null) {
            $result = [];
            foreach ($exps as $i => $exp) {
                if ($string !== null || $i < count($exps) - 1) {
                    $result[] = self::compileToPHPExpressions(trim($exp), true);
                } else {
                    // the last expression can thorw exception.
                    $result[] = self::compileToPHPExpressions(trim($exp), false);
                }
            }
            if ($string !== null) {
                $result[] = self::compileToPHPExpressions($string, true);
            }
            return $result;
        }


        // see if there are subexpressions, but skip interpolated parts, i.e. ${a/b}/c is 2 parts
        if (preg_match('/^((?:[^$\/]+|\$\$|\${[^}]+}|\$))\/(.+)$/s', $expression, $m)) {
            if (!self::checkExpressionPart($m[1])) {
                throw new ParserException("Invalid TALES path: '$expression', expected '{$m[1]}' to be variable name");
            }

            $next = self::string($m[1]);
            $expression = self::string($m[2]);
        } else {
            if (!self::checkExpressionPart($expression)) {
                throw new ParserException("Invalid TALES path: '$expression', expected variable name. Complex expressions need php: modifier.");
            }

            $next = self::string($expression);
            $expression = null;
        }

        if ($nothrow) {
            return '$ctx->path($ctx, ' . $next . ($expression === null ? '' : '."/".' . $expression) . ', true)';
        }

        if (preg_match('/^\'[a-z][a-z0-9_]*\'$/i', $next)) {
            $next = substr($next, 1, -1);
        } else {
            $next = '{' . $next . '}';
        }

        // if no sub part for this expression, just optimize the generated code
        // and access the $ctx->var
        if ($expression === null) {
            return '$ctx->' . $next;
        }

        // otherwise we have to call Context::path() to resolve the path at runtime
        // extract the first part of the expression (it will be the Context::path()
        // $base and pass the remaining of the path to Context::path()
        return '$ctx->path($ctx->' . $next . ', ' . $expression . ')';
    }

    /**
     * check if part of exprssion (/foo/ or /foo${bar}/) is alphanumeric
     *
     * @param string $expression
     *
     * @return false|int
     */
    private static function checkExpressionPart($expression)
    {
        $expression = preg_replace('/\${[^}]+}/', 'a', $expression); // pretend interpolation is done
        return preg_match('/^[a-z_][a-z0-9_]*$/i', $expression);
    }

    /**
     * string:
     *
     *      string_expression ::= ( plain_string | [ varsub ] )*
     *      varsub            ::= ( '$' Path ) | ( '${' Path '}' )
     *      plain_string      ::= ( '$$' | non_dollar )*
     *      non_dollar        ::= any character except '$'
     *
     * Examples:
     *
     *      string:my string
     *      string:hello, $username how are you
     *      string:hello, ${user/name}
     *      string:you have $$130 in your bank account
     *
     * @param string $expression
     * @param bool $nothrow
     *
     * @return null|string|string[]
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function string($expression, $nothrow = false)
    {
        return self::parseString($expression, $nothrow, '');
    }

    /**
     * @param string $expression
     * @param bool $nothrow
     * @param string $tales_prefix prefix added to all TALES in the string
     *
     * @return null|string|string[]
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function parseString($expression, $nothrow, $tales_prefix)
    {
        // This is a simple parser which evaluates ${foo} inside
        // 'string:foo ${foo} bar' expressions, it returns the php code which will
        // print the string with correct interpollations.
        // Nothing special there :)

        $inPath = false;
        $inAccoladePath = false;
        $lastWasDollar = false;
        $result = '';
        $subPath = '';
        $len = strlen($expression);
        for ($i = 0; $i < $len; $i++) {
            $c = $expression[$i];
            switch ($c) {
                case '$':
                    if ($lastWasDollar) {
                        $lastWasDollar = false;
                    } elseif ($inAccoladePath) {
                        $subPath .= $c;
                        $c = '';
                    } else {
                        $lastWasDollar = true;
                        $c = '';
                    }
                    break;

                case '\\':
                    if ($inAccoladePath) {
                        $subPath .= $c;
                        $c = '';
                    } else {
                        $c = '\\\\';
                    }
                    break;

                case '\'':
                    if ($inAccoladePath) {
                        $subPath .= $c;
                        $c = '';
                    } else {
                        $c = '\\\'';
                    }
                    break;

                case '{':
                    if ($inAccoladePath) {
                        $subPath .= $c;
                        $c = '';
                    } elseif ($lastWasDollar) {
                        $lastWasDollar = false;
                        $inAccoladePath = true;
                        $subPath = '';
                        $c = '';
                    }
                    break;

                case '}':
                    if ($inAccoladePath) {
                        $inAccoladePath = false;
                        $subEval = self::compileToPHPExpression($tales_prefix . $subPath);
                        $result .= "'.(" . $subEval . ").'";
                        $subPath = '';
                        $lastWasDollar = false;
                        $c = '';
                    }
                    break;

                default:
                    if ($lastWasDollar) {
                        $lastWasDollar = false;
                        $inPath = true;
                        $subPath = $c;
                        $c = '';
                    } elseif ($inAccoladePath) {
                        $subPath .= $c;
                        $c = '';
                    } elseif ($inPath) {
                        $t = strtolower($c);
                        if (($t >= 'a' && $t <= 'z') || ($t >= '0' && $t <= '9') || ($t === '_')) {
                            $subPath .= $c;
                            $c = '';
                        } else {
                            $inPath = false;
                            $subEval = self::compileToPHPExpression($tales_prefix . $subPath);
                            $result .= "'.(" . $subEval . ").'";
                        }
                    }
                    break;
            }
            $result .= $c;
        }
        if ($inPath) {
            $subEval = self::compileToPHPExpression($tales_prefix . $subPath);
            $result .= "'.(" . $subEval . ").'";
        }

        // optimize ''.foo.'' to foo
        $result = preg_replace("/^(?:''\.)?(.*?)(?:\.'')?$/", '\1', '\'' . $result . '\'');

        /*
            The following expression (with + in first alternative):
            "/^\(((?:[^\(\)]+|\([^\(\)]*\))*)\)$/"

            did work properly for (aaaaaaa)aa, but not for (aaaaaaaaaaaaaaaaaaaaa)aa
            WTF!?
        */

        // optimize (foo()) to foo()
        $result = preg_replace("/^\(((?:[^\(\)]|\([^\(\)]*\))*)\)$/", '\1', $result);

        return $result;
    }

    /**
     * php: modifier.
     *
     * Transform the expression into a regular PHP expression.
     *
     * @param string $src
     *
     * @return string
     * @throws ParserException
     */
    public static function php($src)
    {
        return Transformer::transform($src, '$ctx->');
    }

    /**
     * exists: modifier.
     *
     * Returns the code required to invoke Context::exists() on specified path.
     *
     * @param string $src
     * @param bool $nothrow
     *
     * @return string
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function exists($src, $nothrow)
    {
        $src = trim($src);
        if (ctype_alnum($src)) {
            return 'isset($ctx->' . $src . ')';
        }
        return '(null !== ' . self::compileToPHPExpression($src, true) . ')';
    }

    /**
     * number: modifier.
     *
     * Returns the number as is.
     *
     * @param string $src
     * @param bool $nothrow
     *
     * @return string
     * @throws ParserException
     */
    public static function number($src, $nothrow)
    {
        if (!is_numeric(trim($src))) {
            throw new ParserException("'$src' is not a number");
        }
        return trim($src);
    }

    /**
     * json: modifier. Serializes anything as JSON.
     *
     * @param string $src
     * @param string $nothrow
     *
     * @return string
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function json($src, $nothrow)
    {
        return 'json_encode(' . static::compileToPHPExpression($src, $nothrow) . ')';
    }

    /**
     * urlencode: modifier. Escapes a string.
     *
     * @param string $src
     * @param bool $nothrow
     *
     * @return string
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function urlencode($src, $nothrow)
    {
        return 'rawurlencode(' . static::compileToPHPExpression($src, $nothrow) . ')';
    }

    /**
     * translates TALES expression with alternatives into single PHP expression.
     * Identical to compileToPHPExpressions() for singular expressions.
     *
     * @see \PhpTal\Php\TalesInternal::compileToPHPExpressions()
     *
     * @param string $expression
     * @param bool $nothrow
     *
     * @return string
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function compileToPHPExpression($expression, $nothrow = false)
    {
        $r = self::compileToPHPExpressions($expression, $nothrow);
        if (!is_array($r)) {
            return $r;
        }

        // this weird ternary operator construct is to execute noThrow inside the expression
        return '($ctx->noThrow(true)||1?' . self::convertExpressionsToExpression($r, $nothrow) . ':"")';
    }

    /**
     * @param array $array
     * @param bool $nothrow
     *
     * @return string
     */
    private static function convertExpressionsToExpression(array $array, $nothrow)
    {
        if (count($array) === 1) {
            return '($ctx->noThrow(' . ($nothrow ? 'true' : 'false') . ')||1?(' .
                ($array[0] === self::NOTHING_KEYWORD ? 'null' : $array[0]) .
                '):"")';
        }

        $expr = array_shift($array);

        return "(!\PhpTal\Helper::phptal_isempty(\$_tmp5=$expr) && (\$ctx->noThrow(false)||1)?\$_tmp5:" .
            self::convertExpressionsToExpression($array, $nothrow) . ')';
    }

    /**
     * returns PHP code that will evaluate given TALES expression.
     * e.g. "string:foo${bar}" may be transformed to "'foo'.phptal_escape($ctx->bar)"
     *
     * Expressions with alternatives ("foo | bar") will cause it to return array
     * Use \PhpTal\Php\TalesInternal::compileToPHPExpression() if you always want string.
     *
     * @param string $expression
     * @param bool $nothrow if true, invalid expression will return NULL (at run time) rather than throwing exception
     *
     * @return string or array
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws \ReflectionException
     */
    public static function compileToPHPExpressions($expression, $nothrow = false)
    {
        $expression = trim($expression);
        $typePrefix = null;

        // Look for tales modifier (string:, exists:, Namespaced\Tale:, etc...)
        if (preg_match('/^([a-z](?:[a-z0-9._\\\\-]*[a-z0-9])?):(.*)$/si', $expression, $m)) {
            list(, $typePrefix, $expression) = $m;
        } // may be a 'string'
        elseif (preg_match('/^\'((?:[^\']|\\\\.)*)\'$/s', $expression, $m)) {
            $expression = stripslashes($m[1]);
            $typePrefix = 'string';
        } // failback to path:
        else {
            $typePrefix = 'path';
        }

        // is a registered TALES expression modifier
        $callback = TalesRegistry::getCallback($typePrefix);
        if ($callback !== null) {
            $result = $callback($expression, $nothrow);
            self::verifyPHPExpressions($typePrefix, $result);
            return $result;
        }

        throw new UnknownModifierException(
            sprintf('Unknown phptal modifier %s. Custom modifiers have to be registered explicitly.', $typePrefix)
        );
    }

    /**
     * @param string $typePrefix
     * @param array $expressions
     *
     * @return void
     * @throws ParserException
     */
    private static function verifyPHPExpressions($typePrefix, $expressions)
    {
        if (!is_array($expressions)) {
            $expressions = [$expressions];
        }

        foreach ($expressions as $expr) {
            if (preg_match('/;\s*$/', $expr)) {
                throw new ParserException("Modifier $typePrefix generated PHP statement rather than expression (don't add semicolons)");
            }
        }
    }
}
