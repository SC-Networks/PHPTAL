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

use PhpTal\Dom\Element;
use PhpTal\Exception\ConfigurationException;
use PhpTal\Exception\ParserException;
use PhpTal\Exception\PhpNotAllowedException;
use PhpTal\Exception\PhpTalException;
use PhpTal\Exception\TemplateException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\PHPTAL;
use ReflectionException;

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
     */
    private int $temp_var_counter = 0;

    /**
     * stack with free'd variables
     * @var string[]
     */
    private array $temp_recycling = [];

    /**
     * keeps track of seen functions for function_exists
     * @var array<string, bool>
     */
    private array $known_functions = [];

    /**
     * @var string
     */
    private $result = '';

    /**
     * @var int
     */
    private $indentation = 0;

    /**
     * @var array<int, string>
     */
    private $codeBuffer = [];

    /**
     * @var array<string>
     */
    private $htmlBuffer = [];

    /**
     * @var array<string>
     */
    private $segments = [];

    /**
     * @var array<CodeWriter>
     */
    private array $contexts = [];

    private string $functionPrefix = '';

    private string $doctype = '';

    private string $xmldeclaration = '';

    /**
     * CodeWriter constructor.
     */
    public function __construct(private State $state)
    {
    }

    public function createTempVariable(): string
    {
        if (count($this->temp_recycling)) {
            return array_shift($this->temp_recycling);
        }
        return '$_tmp_' . (++$this->temp_var_counter);
    }

    /**
     * @param string $var
     * @throws PhpTalException
     */
    public function recycleTempVariable($var): void
    {
        if (!str_starts_with($var, '$_tmp_')) {
            throw new PhpTalException('Invalid variable recycled');
        }
        $this->temp_recycling[] = $var;
    }

    public function getCacheFilesBaseName(): string
    {
        return $this->state->getCacheFilesBaseName();
    }

    public function getResult(): string
    {
        $this->flush();
        return trim($this->result);
    }

    /**
     * set full '<!DOCTYPE...>' string to output later
     *
     *
     */
    public function setDocType(string $dt): void
    {
        $this->doctype = $dt;
    }

    /**
     * set full '<?xml ?>' string to output later
     *
     *
     */
    public function setXmlDeclaration(string $dt): void
    {
        $this->xmldeclaration = $dt;
    }

    /**
     * functions later generated and checked for existence will have this prefix added
     * (poor man's namespace)
     *
     *
     */
    public function setFunctionPrefix(string $prefix): void
    {
        $this->functionPrefix = $prefix;
    }

    public function getFunctionPrefix(): string
    {
        return $this->functionPrefix;
    }

    /**
     * @see \PhpTal\Php\State::setTalesMode()
     *
     *
     */
    public function setTalesMode(string $mode): string
    {
        return $this->state->setTalesMode($mode);
    }

    /**
     * @return array<string>
     */
    public function splitExpression(string $src): array
    {
        preg_match_all('/(?:[^;]+|;;)+/sm', $src, $array);
        $array = $array[0];
        foreach ($array as &$a) {
            $a = str_replace(';;', ';', (string) $a);
        }
        return $array;
    }

    /**
     *
     * @return string|array<string>
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws ReflectionException
     * @throws PhpNotAllowedException
     */
    public function evaluateExpression(string $src)
    {
        return $this->state->evaluateExpression($src);
    }

    public function indent(): void
    {
        $this->indentation++;
    }

    public function unindent(): void
    {
        $this->indentation--;
    }

    public function flush(): void
    {
        $this->flushCode();
        $this->flushHtml();
    }

    public function noThrow(bool $bool): void
    {
        if ($bool) {
            $this->pushCode('$ctx->noThrow(true)');
        } else {
            $this->pushCode('$ctx->noThrow(false)');
        }
    }

    public function flushCode(): void
    {
        $count = count($this->codeBuffer);
        if ($count === 0) {
            return;
        }

        // special treatment for one code line
        if ($count === 1) {
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

    public function flushHtml(): void
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
    public function doDoctype(bool $called_from_macro = null): void
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
    public function doXmlDeclaration(bool $called_from_macro = null): void
    {
        if ($this->xmldeclaration && $this->getOutputMode() !== PHPTAL::HTML5) {
            $code = '$ctx->setXmlDeclaration(' . $this->str($this->xmldeclaration) .
                ',' . ($called_from_macro ? 'true' : 'false') . ')';
            $this->pushCode($code);
        }
    }

    public function functionExists(string $name): bool
    {
        return isset($this->known_functions[$this->functionPrefix . $name]);
    }

    /**
     * @throws PhpTalException
     * @throws TemplateException
     */
    public function doTemplateFile(string $functionName, Element $treeGen): void
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

    public function doFunction(string $name, string $params): void
    {
        $name = $this->functionPrefix . $name;
        $this->known_functions[$name] = true;

        $this->pushCodeWriterContext();
        $this->pushCode("function $name($params) {\n");
        $this->indent();
        $this->segments[] = 'function';
    }

    public function doComment(string $comment): void
    {
        $comment = str_replace('*/', '* /', $comment);
        $this->pushCode("/* $comment */");
    }

    public function doInitTranslator(): void
    {
        if ($this->state->isTranslationOn()) {
            $this->doSetVar('$_translator', '$tpl->getTranslator()');
        }
    }

    /**
     * @throws ConfigurationException
     */
    public function getTranslatorReference(): string
    {
        if (!$this->state->isTranslationOn()) {
            throw new ConfigurationException('i18n used, but Translator has not been set');
        }
        return '$_translator';
    }

    public function doEval(string $code): void
    {
        $this->pushCode($code);
    }

    public function doForeach(string $out, string $source): void
    {
        $this->segments[] = 'foreach';
        $this->pushCode("foreach ($source as $out):");
        $this->indent();
    }

    /**
     * @throws PhpTalException
     */
    public function doEnd(string $expects = null): void
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

    public function doTry(): void
    {
        $this->segments[] = 'try';
        $this->pushCode('try {');
        $this->indent();
    }

    public function doSetVar(string $varname, string $code): void
    {
        $this->pushCode($varname . ' = ' . $code);
    }

    /**
     * @throws PhpTalException
     */
    public function doCatch(string $catch): void
    {
        $this->doEnd('try');
        $this->segments[] = 'catch';
        $this->pushCode('catch(' . $catch . ') {');
        $this->indent();
    }

    public function doIf(string $condition): void
    {
        $this->segments[] = 'if';
        $this->pushCode('if (' . $condition . '): ');
        $this->indent();
    }

    /**
     * @throws PhpTalException
     */
    public function doElseIf(string $condition): void
    {
        if (end($this->segments) !== 'if') {
            throw new PhpTalException('Bug: CodeWriter generated elseif without if');
        }
        $this->unindent();
        $this->pushCode('elseif (' . $condition . '): ');
        $this->indent();
    }

    /**
     * @throws PhpTalException
     */
    public function doElse(): void
    {
        if (end($this->segments) !== 'if') {
            throw new PhpTalException('Bug: CodeWriter generated else without if');
        }
        $this->unindent();
        $this->pushCode('else: ');
        $this->indent();
    }

    public function doEcho(string $code): void
    {
        if ($code === "''") {
            return;
        }
        $this->flush();
        $this->pushCode('echo ' . $this->escapeCode($code));
    }

    public function doEchoRaw(string $code): void
    {
        if ($code === "''") {
            return;
        }
        $this->pushCode('echo ' . $this->stringifyCode($code));
    }

    public function interpolateHTML(string $html): string
    {
        return $this->state->interpolateTalesVarsInHTML($html);
    }

    public function interpolateCDATA(string $str): string
    {
        return $this->state->interpolateTalesVarsInCDATA($str);
    }

    public function pushHTML(string $html): void
    {
        if ($html === '') {
            return;
        }
        $this->flushCode();
        $this->htmlBuffer[] = $html;
    }

    public function pushCode(string $codeLine): void
    {
        $this->flushHtml();
        $codeLine = $this->indentSpaces() . $codeLine;
        $this->codeBuffer[] = $codeLine;
    }

    /**
     * php string with escaped text
     *
     *
     */
    public function str(string $string): string
    {
        return "'" . strtr($string, ["'" => '\\\'', '\\' => '\\\\']) . "'";
    }

    public function escapeCode(string $code): string
    {
        return $this->state->htmlchars($code);
    }

    public function stringifyCode(string $code): string
    {
        return $this->state->stringify($code);
    }

    public function getEncoding(): string
    {
        return $this->state->getEncoding();
    }

    /**
     *
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    public function interpolateTalesVarsInString(string $src): string
    {
        return $this->state->interpolateTalesVarsInString($src);
    }

    public function setDebug(bool $bool): bool
    {
        return $this->state->setDebug($bool);
    }

    public function isDebugOn(): bool
    {
        return $this->state->isDebugOn();
    }

    public function getOutputMode(): int
    {
        return $this->state->getOutputMode();
    }

    public function quoteAttributeValue(string $value): string
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

    public function pushContext(): void
    {
        $this->doSetVar('$ctx', '$tpl->pushContext()');
    }

    public function popContext(): void
    {
        $this->doSetVar('$ctx', '$tpl->popContext()');
    }

    private function indentSpaces(): string
    {
        return str_repeat("\t", $this->indentation);
    }

    private function pushCodeWriterContext(): void
    {
        $this->contexts[] = clone $this;
        $this->result = '';
        $this->indentation = 0;
        $this->codeBuffer = [];
        $this->htmlBuffer = [];
        $this->segments = [];
    }

    private function popCodeWriterContext(): void
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
