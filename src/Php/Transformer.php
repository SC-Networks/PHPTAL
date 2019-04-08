<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 */

namespace PhpTal\Php;

use PhpTal\Exception\ParserException;

/**
 * Tranform php: expressions into their php equivalent.
 *
 * This transformer produce php code for expressions like :
 *
 * - a.b["key"].c().someVar[10].foo()
 * - (a or b) and (c or d)
 * - not myBool
 * - ...
 *
 * The $prefix variable may be changed to change the context lookup.
 *
 * example:
 *
 *      $res = \PhpTal\Php\Transformer::transform('a.b.c[x]', '$ctx->');
 *      $res == '$ctx->a->b->c[$ctx->x]';
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class Transformer
{
    public const ST_WHITE = -1; // start of string or whitespace
    public const ST_NONE = 0;  // pass through (operators, parens, etc.)
    public const ST_STR = 1;  // 'foo'
    public const ST_ESTR = 2;  // "foo ${x} bar"
    public const ST_VAR = 3;  // abcd
    public const ST_NUM = 4;  // 123.02
    public const ST_EVAL = 5;  // $somevar
    public const ST_MEMBER = 6;  // abcd.x
    public const ST_STATIC = 7;  // class::[$]static|const
    public const ST_DEFINE = 8;  // @MY_DEFINE

    /**
     * @var array
     */
    private static $TranslationTable = [
        'not' => '!',
        'ne' => '!=',
        'and' => '&&',
        'or' => '||',
        'lt' => '<',
        'gt' => '>',
        'ge' => '>=',
        'le' => '<=',
        'eq' => '==',
    ];

    /**
     * transform PHPTAL's php-like syntax into real PHP
     *
     * @param string $str
     * @param string $prefix
     *
     * @return string
     * @throws ParserException
     */
    public static function transform(string $str, ?string $prefix = null): string
    {
        $prefix = $prefix ?? '$';
        $len = strlen($str);
        $state = self::ST_WHITE;
        $result = '';
        $backslashed = false;
        $instanceof = false;
        $eval = false;
        $mark = 0;


        for ($i = 0; $i <= $len; $i++) {
            if ($i === $len) {
                $c = "\0";
            } else {
                $c = $str[$i];
            }

            switch ($state) {
                // after whitespace a variable-variable may start, ${var} → $ctx->{$ctx->var}
                case self::ST_WHITE:
                    if ($c === '$' && $i + 1 < $len && $str[$i + 1] === '{') {
                        $result .= $prefix;
                        $state = self::ST_NONE;
                        continue 2;
                    }
                /* NO BREAK - ST_WHITE is almost the same as ST_NONE */

                // no specific state defined, just eat char and see what to do with it.
                case self::ST_NONE:
                    // begin of eval without {
                    if ($c === '$' && $i + 1 < $len && self::isAlpha($str[$i + 1])) {
                        $state = self::ST_EVAL;
                        $mark = $i + 1;
                        $result .= $prefix . '{';
                    } elseif (self::isDigit($c)) {
                        $state = self::ST_NUM;
                        $mark = $i;
                    } elseif (self::isVarNameChar($c)) {
                        // that an alphabetic char, then it should be the begining
                        // of a var or static
                        // && !self::isDigit($c) checked earlier
                        $state = self::ST_VAR;
                        $mark = $i;
                    } elseif ($c === '"') { // begining of double quoted string
                        $state = self::ST_ESTR;
                        $mark = $i;
                    } elseif ($c === '\'') { // begining of single quoted string
                        $state = self::ST_STR;
                        $mark = $i;
                    } elseif (in_array($c, [')', ']', '}'], true)) {
                        // closing a method, an array access or an evaluation
                        $result .= $c;
                        // if next char is dot then an object member must
                        // follow
                        if ($i + 1 < $len && $str[$i + 1] === '.') {
                            $result .= '->';
                            $state = self::ST_MEMBER;
                            $mark = $i + 2;
                            $i += 2;
                        }
                    } elseif ($c === '@') { // @ is an access to some defined variable
                        $state = self::ST_DEFINE;
                        $mark = $i + 1;
                    } elseif (ctype_space($c)) {
                        $state = self::ST_WHITE;
                        $result .= $c;
                    } else { // character we don't mind about
                        $result .= $c;
                    }
                    break;

                // $xxx
                case self::ST_EVAL:
                    if (!self::isVarNameChar($c)) {
                        $result .= $prefix . substr($str, $mark, $i - $mark);
                        $result .= '}';
                        $state = self::ST_NONE;
                    }
                    break;

                // single quoted string
                case self::ST_STR:
                    if ($c === '\\') {
                        $backslashed = true;
                    } elseif ($backslashed) {
                        $backslashed = false;
                    } // end of string, back to none state
                    elseif ($c === '\'') {
                        $result .= substr($str, $mark, $i - $mark + 1);
                        $state = self::ST_NONE;
                    }
                    break;

                // double quoted string
                case self::ST_ESTR:
                    if ($c === '\\') {
                        $backslashed = true;
                    } elseif ($backslashed) {
                        $backslashed = false;
                    } // end of string, back to none state
                    elseif ($c === '"') {
                        $result .= substr($str, $mark, $i - $mark + 1);
                        $state = self::ST_NONE;
                    } elseif ($c === '$' && $i + 1 < $len && $str[$i + 1] === '{') {
                        // instring interpolation, search } and transform the
                        // interpollation to insert it into the string
                        $result .= substr($str, $mark, $i - $mark) . '{';

                        $sub = 0;
                        for ($j = $i; $j < $len; $j++) {
                            if ($str[$j] === '{') {
                                $sub++;
                            } elseif ($str[$j] === '}' && (--$sub) === 0) {
                                $part = substr($str, $i + 2, $j - $i - 2);
                                $result .= self::transform($part, $prefix);
                                $i = $j;
                                $mark = $i;
                            }
                        }
                    }
                    break;

                // var state
                case self::ST_VAR:
                    if (self::isVarNameChar($c)) {
                        // noop
                    } // end of var, begin of member (method or var)
                    elseif ($c === '.') {
                        $result .= $prefix . substr($str, $mark, $i - $mark);
                        $result .= '->';
                        $state = self::ST_MEMBER;
                        $mark = $i + 1;
                    } elseif ($c === ':' && $i + 1 < $len && $str[$i + 1] === ':') {
                        // static call, the var is a class name
                        $result .= substr($str, $mark, $i - $mark + 1);
                        $mark = $i + 1;
                        $i++;
                        $state = self::ST_STATIC;
                        break;
                    } elseif ($c === '(') {
                        // function invocation, the var is a function name
                        $result .= substr($str, $mark, $i - $mark + 1);
                        $state = self::ST_NONE;
                    } elseif ($c === '[') { // array index, the var is done
                        if ($str[$mark] === '_') { // superglobal?
                            $result .= '$' . substr($str, $mark, $i - $mark + 1);
                        } else {
                            $result .= $prefix . substr($str, $mark, $i - $mark + 1);
                        }
                        $state = self::ST_NONE;
                    } else {
                        // end of var with non-var-name character, handle keywords
                        // and populate the var name

                        $var = substr($str, $mark, $i - $mark);
                        $low = strtolower($var);
                        // boolean and null
                        if (in_array($low, ['true', 'false', 'null'], true)) {
                            $result .= $var;
                        } elseif (array_key_exists($low, self::$TranslationTable)) {
                            // lt, gt, ge, eq, ...
                            $result .= self::$TranslationTable[$low];
                        } elseif ($low === 'instanceof') {
                            // instanceof keyword
                            $result .= $var;
                            $instanceof = true;
                        } elseif ($instanceof) {
                            // previous was instanceof
                            // last was instanceof, this var is a class name
                            $result .= $var;
                            $instanceof = false;
                        } else {
                            // regular variable
                            $result .= $prefix . $var;
                        }
                        $i--;
                        $state = self::ST_NONE;
                    }
                    break;

                // object member
                case self::ST_MEMBER:
                    if (self::isVarNameChar($c)) {
                        // noop
                    } // eval mode ${foo}
                    elseif ($c === '$' && ($i >= $len - 2 || $str[$i + 1] !== '{')) {
                        $result .= '{' . $prefix;
                        $mark++;
                        $eval = true;
                    } elseif ($c === '$') {
                        // x.${foo} x->{foo}
                        $mark++;
                    } elseif ($c === '.') {
                        // end of var member var, begin of new member
                        $result .= substr($str, $mark, $i - $mark);
                        if ($eval) {
                            $result .= '}';
                            $eval = false;
                        }
                        $result .= '->';
                        $mark = $i + 1;
                        $state = self::ST_MEMBER;
                    } elseif ($c === ':') {
                        // begin of static access
                        $result .= substr($str, $mark, $i - $mark + 1);
                        if ($eval) {
                            $result .= '}';
                            $eval = false;
                        }
                        $state = self::ST_STATIC;
                        break;
                    } elseif ($c === '(' || $c === '[') {
                        // the member is a method or an array
                        $result .= substr($str, $mark, $i - $mark + 1);
                        if ($eval) {
                            $result .= '}';
                            $eval = false;
                        }
                        $state = self::ST_NONE;
                    } else {
                        // regular end of member, it is a var
                        $var = substr($str, $mark, $i - $mark);
                        if ($var !== '' && !preg_match('/^[a-z][a-z0-9_\x7f-\xff]*$/i', $var)) {
                            throw new ParserException("Invalid field name '$var' in expression php:$str");
                        }
                        $result .= $var;
                        if ($eval) {
                            $result .= '}';
                            $eval = false;
                        }
                        $state = self::ST_NONE;
                        $i--;
                    }
                    break;

                // wait for separator
                case self::ST_DEFINE:
                    if (self::isVarNameChar($c)) {
                        // noop
                    } else {
                        $state = self::ST_NONE;
                        $result .= substr($str, $mark, $i - $mark);
                        $i--;
                    }
                    break;

                // static call, can be const, static var, static method
                // Klass::$static
                // Klass::const
                // Kclass::staticMethod()
                //
                case self::ST_STATIC:
                    if (self::isVarNameChar($c)) {
                        // noop
                    } // static var
                    elseif ($c === '$') {
                        // noop
                    } // end of static var which is an object and begin of member
                    elseif ($c === '.') {
                        $result .= substr($str, $mark, $i - $mark);
                        $result .= '->';
                        $mark = $i + 1;
                        $state = self::ST_MEMBER;
                    } elseif ($c === ':') {
                        // end of static var which is a class name
                        $result .= substr($str, $mark, $i - $mark + 1);
                        $state = self::ST_STATIC;
                        break;
                    } elseif ($c === '(' || $c === '[') {
                        // static method or array
                        $result .= substr($str, $mark, $i - $mark + 1);
                        $state = self::ST_NONE;
                    } else {
                        // end of static var or const
                        $result .= substr($str, $mark, $i - $mark);
                        $state = self::ST_NONE;
                        $i--;
                    }
                    break;

                // numeric value
                case self::ST_NUM:
                    if (!self::isDigitCompound($c)) {
                        $var = substr($str, $mark, $i - $mark);

                        if (self::isAlpha($c) || $c === '_') {
                            throw new ParserException("Syntax error in number '$var$c' in expression php:$str");
                        }
                        if (!is_numeric($var)) {
                            throw new ParserException("Syntax error in number '$var' in expression php:$str");
                        }

                        $result .= $var;
                        $state = self::ST_NONE;
                        $i--;
                    }
                    break;
            }
        }

        $result = trim($result);

        // CodeWriter doesn't like expressions that look like blocks
        if ($result[strlen($result) - 1] === '}') {
            return '(' . $result . ')';
        }

        return $result;
    }

    /**
     * @param string $c
     *
     * @return bool
     */
    private static function isAlpha(string $c): bool
    {
        $c = strtolower($c);
        return $c >= 'a' && $c <= 'z';
    }

    /**
     * @param string $c
     *
     * @return bool
     */
    private static function isDigit(string $c): bool
    {
        return ($c >= '0' && $c <= '9');
    }

    /**
     * @param string $c
     *
     * @return bool
     */
    private static function isDigitCompound(string $c): bool
    {
        return (static::isDigit($c) || $c === '.');
    }

    /**
     * @param string $c
     *
     * @return bool
     */
    private static function isVarNameChar(string $c): bool
    {
        return static::isAlpha($c) || static::isDigit($c) || $c === '_' || $c === '\\';
    }
}
