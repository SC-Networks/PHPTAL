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

namespace PhpTal\Php;

use PhpTal\Context;
use PhpTal\Dom\Element;
use PhpTal\Exception\ConfigurationException;
use PhpTal\Exception\PhpTalException;
use PhpTal\PHPTAL;

/**
 * Helps generate php representation of a template.
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class CodeWriter
{
    /**
     * max id of variable to give as temp
     * @var int
     */
    private $temp_var_counter = 0;

    /**
     * stack with free'd variables
     * @var string[]
     */
    private $temp_recycling = [];

    /**
     * keeps track of seen functions for function_exists
     * @var array
     */
    private $known_functions = [];

    /**
     * @var State
     */
    private $state;

    /**
     * @var string
     */
    private $result = '';

    /**
     * @var int
     */
    private $indentation = 0;

    /**
     * @var array
     */
    private $codeBuffer = [];

    /**
     * @var array
     */
    private $htmlBuffer = [];

    /**
     * @var array
     */
    private $segments = [];

    /**
     * @var array
     */
    private $contexts = [];

    /**
     * @var string
     */
    private $functionPrefix = '';

    /**
     * @var string
     */
    private $doctype = '';

    /**
     * @var string
     */
    private $xmldeclaration = '';

    /**
     * CodeWriter constructor.
     * @param State $state
     */
    public function __construct(State $state)
    {
        $this->state = $state;
    }

    /**
     * @return string
     */
    public function createTempVariable()
    {
        if (count($this->temp_recycling)) {
            return array_shift($this->temp_recycling);
        }
        return '$_tmp_' . (++$this->temp_var_counter);
    }

    /**
     * @param string $var
     * @return void
     * @throws PhpTalException
     */
    public function recycleTempVariable($var)
    {
        if (strpos($var, '$_tmp_') !== 0) {
            throw new PhpTalException('Invalid variable recycled');
        }
        $this->temp_recycling[] = $var;
    }

    /**
     * @return string
     */
    public function getCacheFilesBaseName()
    {
        return $this->state->getCacheFilesBaseName();
    }

    /**
     * @return string
     */
    public function getResult()
    {
        $this->flush();
        return trim($this->result);
    }

    /**
     * set full '<!DOCTYPE...>' string to output later
     *
     * @param string $dt
     *
     * @return void
     */
    public function setDocType($dt)
    {
        $this->doctype = $dt;
    }

    /**
     * set full '<?xml ?>' string to output later
     *
     * @param string $dt
     *
     * @return void
     */
    public function setXmlDeclaration($dt)
    {
        $this->xmldeclaration = $dt;
    }

    /**
     * functions later generated and checked for existence will have this prefix added
     * (poor man's namespace)
     *
     * @param string $prefix
     *
     * @return void
     */
    public function setFunctionPrefix($prefix)
    {
        $this->functionPrefix = $prefix;
    }

    /**
     * @return string
     */
    public function getFunctionPrefix()
    {
        return $this->functionPrefix;
    }

    /**
     * @see \PhpTal\Php\State::setTalesMode()
     *
     * @param string $mode
     *
     * @return string
     */
    public function setTalesMode($mode)
    {
        return $this->state->setTalesMode($mode);
    }

    /**
     * @param string $src
     *
     * @return array
     */
    public function splitExpression($src)
    {
        preg_match_all('/(?:[^;]+|;;)+/sm', $src, $array);
        $array = $array[0];
        foreach ($array as &$a) {
            $a = str_replace(';;', ';', $a);
        }
        return $array;
    }

    /**
     * @param string $src
     *
     * @return string
     * @throws \PhpTal\Exception\ParserException
     * @throws \PhpTal\Exception\UnknownModifierException
     * @throws \ReflectionException
     */
    public function evaluateExpression($src)
    {
        return $this->state->evaluateExpression($src);
    }

    /**
     * @return void
     */
    public function indent()
    {
        $this->indentation++;
    }

    /**
     * @return void
     */
    public function unindent()
    {
        $this->indentation--;
    }

    /**
     * @return void
     */
    public function flush()
    {
        $this->flushCode();
        $this->flushHtml();
    }

    /**
     * @param bool $bool
     * @return void
     */
    public function noThrow($bool)
    {
        if ($bool) {
            $this->pushCode('$ctx->noThrow(true)');
        } else {
            $this->pushCode('$ctx->noThrow(false)');
        }
    }

    /**
     * @return void
     */
    public function flushCode()
    {
        if (count($this->codeBuffer) === 0) {
            return;
        }

        // special treatment for one code line
        if (count($this->codeBuffer) === 1) {
            $codeLine = $this->codeBuffer[0];
            // avoid adding ; after } and {
            if (!preg_match('/\}\s*$|\{\s*$/', $codeLine)) {
                $this->result .= '<?php ' . $codeLine . "; ?>\n";
            } // PHP consumes newline
            else {
                $this->result .= '<?php ' . $codeLine . " ?>\n";
            } // PHP consumes newline
            $this->codeBuffer = [];
            return;
        }

        $this->result .= '<?php ' . "\n";
        foreach ($this->codeBuffer as $codeLine) {
            // avoid adding ; after } and {
            if (!preg_match('/[{};]\s*$/', $codeLine)) {
                $codeLine .= ' ;' . "\n";
            }
            $this->result .= $codeLine;
        }
        $this->result .= "?>\n";// PHP consumes newline
        $this->codeBuffer = [];
    }

    /**
     * @return void
     */
    public function flushHtml()
    {
        if (count($this->htmlBuffer) === 0) {
            return;
        }

        $this->result .= implode('', $this->htmlBuffer);
        $this->htmlBuffer = [];
    }

    /**
     * Generate code for setting DOCTYPE
     *
     * @param bool $called_from_macro for error checking: unbuffered output doesn't support that
     */
    public function doDoctype($called_from_macro = false)
    {
        if ($this->doctype) {
            $code = '$ctx->setDocType(' . $this->str($this->doctype) .
                ',' . ($called_from_macro ? 'true' : 'false') . ')';
            $this->pushCode($code);
        }
    }

    /**
     * Generate XML declaration
     *
     * @param bool $called_from_macro for error checking: unbuffered output doesn't support that
     */
    public function doXmlDeclaration($called_from_macro = false)
    {
        if ($this->xmldeclaration && $this->getOutputMode() !== PHPTAL::HTML5) {
            $code = '$ctx->setXmlDeclaration(' . $this->str($this->xmldeclaration) .
                ',' . ($called_from_macro ? 'true' : 'false') . ')';
            $this->pushCode($code);
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function functionExists($name)
    {
        return isset($this->known_functions[$this->functionPrefix . $name]);
    }

    /**
     * @param string $functionName
     * @param Element $treeGen
     * @return void
     * @throws PhpTalException
     * @throws \PhpTal\Exception\TemplateException
     */
    public function doTemplateFile($functionName, Element $treeGen)
    {
        $this->doComment(
            "\n*** DO NOT EDIT THIS FILE ***\n\nGenerated by PHPTAL from " . $treeGen->getSourceFile() .
            ' (edit that file instead)'
        );
        $this->doFunction($functionName, '\PhpTal\PHPTAL $tpl, \PhpTal\Context $ctx');
        $this->setFunctionPrefix($functionName . '_');
        $this->doSetVar('$_thistpl', '$tpl');
        $this->doInitTranslator();
        $treeGen->generateCode($this);
        $this->doComment('end');
        $this->doEnd('function');
    }

    /**
     * @param string $name
     * @param string $params
     *
     * @return void
     */
    public function doFunction($name, $params)
    {
        $name = $this->functionPrefix . $name;
        $this->known_functions[$name] = true;

        $this->pushCodeWriterContext();
        $this->pushCode("function $name($params) {\n");
        $this->indent();
        $this->segments[] = 'function';
    }

    /**
     * @param string $comment
     * @return void
     */
    public function doComment($comment)
    {
        $comment = str_replace('*/', '* /', $comment);
        $this->pushCode("/* $comment */");
    }

    /**
     * @return void
     */
    public function doInitTranslator()
    {
        if ($this->state->isTranslationOn()) {
            $this->doSetVar('$_translator', '$tpl->getTranslator()');
        }
    }

    /**
     * @return string
     * @throws ConfigurationException
     */
    public function getTranslatorReference()
    {
        if (!$this->state->isTranslationOn()) {
            throw new ConfigurationException('i18n used, but Translator has not been set');
        }
        return '$_translator';
    }

    /**
     * @param string $code
     *
     * @return void
     */
    public function doEval($code)
    {
        $this->pushCode($code);
    }

    /**
     * @param string $out
     * @param string $source
     *
     * @return void
     */
    public function doForeach($out, $source)
    {
        $this->segments[] = 'foreach';
        $this->pushCode("foreach ($source as $out):");
        $this->indent();
    }

    /**
     * @param string $expects
     *
     * @return void
     * @throws PhpTalException
     */
    public function doEnd($expects = null)
    {
        if (count($this->segments) === 0) {
            if ($expects === null) {
                $expects = 'anything';
            }
            throw new PhpTalException("Bug: CodeWriter generated end of block without $expects open");
        }

        $segment = array_pop($this->segments);
        if ($expects !== null && $segment !== $expects) {
            throw new PhpTalException("Bug: CodeWriter generated end of $expects, but needs to close $segment");
        }

        $this->unindent();
        if ($segment === 'function') {
            $this->pushCode("\n}\n\n");
            $this->flush();
            $functionCode = $this->result;
            $this->popCodeWriterContext();
            $this->result = $functionCode . $this->result;
        } elseif ($segment === 'try') {
            $this->pushCode('}');
        } elseif ($segment === 'catch') {
            $this->pushCode('}');
        } else {
            $this->pushCode("end$segment");
        }
    }

    /**
     * @return void
     */
    public function doTry()
    {
        $this->segments[] = 'try';
        $this->pushCode('try {');
        $this->indent();
    }

    /**
     * @param string $varname
     * @param string $code
     *
     * @return void
     */
    public function doSetVar($varname, $code)
    {
        $this->pushCode($varname . ' = ' . $code);
    }

    /**
     * @param string $catch
     *
     * @return void
     * @throws PhpTalException
     */
    public function doCatch($catch)
    {
        $this->doEnd('try');
        $this->segments[] = 'catch';
        $this->pushCode('catch(' . $catch . ') {');
        $this->indent();
    }

    /**
     * @param string $condition
     *
     * @return void
     */
    public function doIf($condition)
    {
        $this->segments[] = 'if';
        $this->pushCode('if (' . $condition . '): ');
        $this->indent();
    }

    /**
     * @param string $condition
     *
     * @return void
     * @throws PhpTalException
     */
    public function doElseIf($condition)
    {
        if (end($this->segments) !== 'if') {
            throw new PhpTalException('Bug: CodeWriter generated elseif without if');
        }
        $this->unindent();
        $this->pushCode('elseif (' . $condition . '): ');
        $this->indent();
    }

    /**
     * @return void
     * @throws PhpTalException
     */
    public function doElse()
    {
        if (end($this->segments) !== 'if') {
            throw new PhpTalException('Bug: CodeWriter generated else without if');
        }
        $this->unindent();
        $this->pushCode('else: ');
        $this->indent();
    }

    /**
     * @param string $code
     *
     * @return void
     */
    public function doEcho($code)
    {
        if ($code === "''") {
            return;
        }
        $this->flush();
        $this->pushCode('echo ' . $this->escapeCode($code));
    }

    /**
     * @param string $code
     *
     * @return void
     */
    public function doEchoRaw($code)
    {
        if ($code === "''") {
            return;
        }
        $this->pushCode('echo ' . $this->stringifyCode($code));
    }

    /**
     * @param string $html
     *
     * @return string
     */
    public function interpolateHTML($html)
    {
        return $this->state->interpolateTalesVarsInHTML($html);
    }

    /**
     * @param string $str
     *
     * @return string
     */
    public function interpolateCDATA($str)
    {
        return $this->state->interpolateTalesVarsInCDATA($str);
    }

    /**
     * @param string $html
     *
     * @return void
     */
    public function pushHTML($html)
    {
        if ($html === '') {
            return;
        }
        $this->flushCode();
        $this->htmlBuffer[] = $html;
    }

    /**
     * @param string $codeLine
     *
     * @return void
     */
    public function pushCode($codeLine)
    {
        $this->flushHtml();
        $codeLine = $this->indentSpaces() . $codeLine;
        $this->codeBuffer[] = $codeLine;
    }

    /**
     * php string with escaped text
     * @param string $string
     * @return string
     */
    public function str($string)
    {
        return "'" . strtr($string, ["'" => '\\\'', '\\' => '\\\\']) . "'";
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function escapeCode($code)
    {
        return $this->state->htmlchars($code);
    }

    /**
     * @param string $code
     *
     * @return string
     */
    public function stringifyCode($code)
    {
        return $this->state->stringify($code);
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->state->getEncoding();
    }

    /**
     * @param string $src
     *
     * @return string
     */
    public function interpolateTalesVarsInString($src)
    {
        return $this->state->interpolateTalesVarsInString($src);
    }

    /**
     * @param bool $bool
     *
     * @return bool
     */
    public function setDebug($bool)
    {
        return $this->state->setDebug($bool);
    }

    /**
     * @return bool
     */
    public function isDebugOn()
    {
        return $this->state->isDebugOn();
    }

    /**
     * @return int
     */
    public function getOutputMode()
    {
        return $this->state->getOutputMode();
    }

    /**
     * @param string $value
     * @return string
     */
    public function quoteAttributeValue($value)
    {
        // FIXME: interpolation is done _after_ that function, so ${} must be forbidden for now

        if ($this->getEncoding() === 'UTF-8') {
            // regex excludes unicode control characters, all kinds of whitespace and unsafe characters
            // and trailing / to avoid confusion with self-closing syntax
            $unsafe_attr_regex = '/^$|[&=\'"><\s`\pM\pC\pZ\p{Pc}\p{Sk}]|\/$|\${/u';
        } else {
            $unsafe_attr_regex = '/^$|[&=\'"><\s`\0177-\377]|\/$|\${/';
        }

        if ($this->getOutputMode() === PHPTAL::HTML5 && !preg_match($unsafe_attr_regex, $value)) {
            return $value;
        }

        return '"' . $value . '"';
    }

    /**
     * @return void
     */
    public function pushContext()
    {
        $this->doSetVar('$ctx', '$tpl->pushContext()');
    }

    /**
     * @return void
     */
    public function popContext()
    {
        $this->doSetVar('$ctx', '$tpl->popContext()');
    }

    /**
     * @return string
     */
    private function indentSpaces()
    {
        return str_repeat("\t", $this->indentation);
    }

    /**
     * @return void
     */
    private function pushCodeWriterContext()
    {
        $this->contexts[] = clone $this;
        $this->result = '';
        $this->indentation = 0;
        $this->codeBuffer = [];
        $this->htmlBuffer = [];
        $this->segments = [];
    }

    /**
     * @return void
     */
    private function popCodeWriterContext()
    {
        /** @var CodeWriter $oldContext TODO ???*/
        $oldContext = array_pop($this->contexts);
        $this->result = $oldContext->result;
        $this->indentation = $oldContext->indentation;
        $this->codeBuffer = $oldContext->codeBuffer;
        $this->htmlBuffer = $oldContext->htmlBuffer;
        $this->segments = $oldContext->segments;
    }
}
