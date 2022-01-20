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
use PhpTal\Exception\PhpNotAllowedException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\PHPTAL;
use PhpTal\PhpTalInterface;
use ReflectionException;

/**
 * @package PHPTAL
 */
class State
{
    /**
     * @var bool
     */
    private $debug = false;

    /**
     * @var string
     */
    private $tales_mode = 'tales';

    /**
     * @var string
     */
    private $encoding;

    /**
     * @var int
     */
    private $output_mode;

    /**
     * @var PhpTalInterface
     */
    private $phptal;

    /**
     * State constructor.
     *
     * @param PhpTalInterface $phptal
     */
    public function __construct(PhpTalInterface $phptal)
    {
        $this->phptal = $phptal;
        $this->encoding = $phptal->getEncoding();
        $this->output_mode = $phptal->getOutputMode();
    }

    /**
     * used by codewriter to get information for phptal:cache
     */
    public function getCacheFilesBaseName(): string
    {
        return $this->phptal->getCodePath();
    }

    /**
     * true if PHPTAL has translator set
     */
    public function isTranslationOn(): bool
    {
        return (bool) $this->phptal->getTranslator();
    }

    /**
     * controlled by phptal:debug
     *
     * @param bool $bool
     *
     * @return bool
     */
    public function setDebug(bool $bool): bool
    {
        $old = $this->debug;
        $this->debug = $bool;
        return $old;
    }

    /**
     * if true, add additional diagnostic information to generated code
     *
     * @return bool
     */
    public function isDebugOn(): bool
    {
        return $this->debug;
    }

    /**
     * Sets new and returns old TALES mode.
     * Valid modes are 'tales' and 'php'
     *
     * @param string $mode
     *
     * @return string
     */
    public function setTalesMode(string $mode): string
    {
        $old = $this->tales_mode;
        $this->tales_mode = $mode;
        return $old;
    }

    /**
     * @return string
     */
    public function getTalesMode(): string
    {
        return $this->tales_mode;
    }

    /**
     * encoding used for both template input and output
     *
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Syntax rules to follow in generated code
     *
     * @return int one of \PhpTal\PHPTAL::XHTML, \PhpTal\PHPTAL::XML, \PhpTal\PHPTAL::HTML5
     */
    public function getOutputMode(): int
    {
        return $this->output_mode;
    }

    /**
     * compile TALES expression according to current talesMode
     *
     * @param null|string $expression
     *
     * @return string|array<string> string with PHP code or array with expressions for TalesChainExecutor
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws ReflectionException
     * @throws PhpNotAllowedException
     */
    public function evaluateExpression(?string $expression)
    {
        if ($this->getTalesMode() === 'php') {
            return TalesInternal::php($expression);
        }
        return TalesInternal::compileToPHPExpressions($expression);
    }

    /**
     * compile TALES expression according to current talesMode
     *
     * @param string $expression
     *
     * @return string with PHP code
     * @throws ParserException
     * @throws PhpNotAllowedException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    private function compileTalesToPHPExpression($expression): string
    {
        if ($this->getTalesMode() === 'php') {
            return TalesInternal::php($expression);
        }
        return TalesInternal::compileToPHPExpression($expression);
    }

    /**
     * returns PHP code that generates given string, including dynamic replacements
     *
     * It's almost unused.
     *
     * @param string $string
     *
     * @return string
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    public function interpolateTalesVarsInString(string $string): string
    {
        return TalesInternal::parseString($string, false, ($this->getTalesMode() === 'tales') ? '' : 'php:');
    }

    /**
     * replaces ${} in string, expecting HTML-encoded input and HTML-escapes output
     *
     * @param string $src
     *
     * @return string
     */
    public function interpolateTalesVarsInHTML(string $src): string
    {
        return preg_replace_callback(
            '/((?:\$\$)*)\$\{(structure |text )?(.*?)\}|((?:\$\$)+)\{/isS',
            [$this, 'interpolateTalesVarsInHTMLCallback'],
            $src
        );
    }

    /**
     * callback for interpolating TALES with HTML-escaping
     *
     * @param array<int, string> $matches
     *
     * @return string
     * @throws ParserException
     * @throws UnknownModifierException
     */
    private function interpolateTalesVarsInHTMLCallback(array $matches): string
    {
        return $this->interpolateTalesVarsCallback($matches, 'html');
    }

    /**
     * replaces ${} in string, expecting CDATA (basically unescaped) input,
     * generates output protected against breaking out of CDATA in XML/HTML
     * (depending on current output mode).
     *
     * @param string $src
     *
     * @return string
     */
    public function interpolateTalesVarsInCDATA(string $src): string
    {
        return preg_replace_callback(
            '/((?:\$\$)*)\$\{(structure |text )?(.*?)\}|((?:\$\$)+)\{/isS',
            [$this, 'interpolateTalesVarsInCDATACallback'],
            $src
        );
    }

    /**
     * callback for interpolating TALES with CDATA escaping
     *
     * @param array<int, string> $matches
     *
     * @return string
     * @throws ParserException
     * @throws UnknownModifierException
     */
    private function interpolateTalesVarsInCDATACallback(array $matches): string
    {
        return $this->interpolateTalesVarsCallback($matches, 'cdata');
    }

    /**
     * @param array<int, string> $matches
     * @param string $format
     *
     * @return string
     * @throws ParserException
     * @throws PhpNotAllowedException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    private function interpolateTalesVarsCallback(array $matches, string $format): string
    {
        // replaces $${ with literal ${ (or $$$${ with $${ etc)
        if (!empty($matches[4])) {
            return substr($matches[4], strlen($matches[4]) / 2) . '{';
        }

        // same replacement, but before executed expression
        $dollars = substr($matches[1], strlen($matches[1]) / 2);

        $code = $matches[3];
        if ($format === 'html') {
            $code = html_entity_decode($code, ENT_QUOTES, $this->getEncoding());
        }

        $code = $this->compileTalesToPHPExpression($code);

        if (rtrim($matches[2]) === 'structure') { // regex captures a space there
            return $dollars . '<?php echo ' . $this->stringify($code) . " ?>\n";
        }

        if ($format === 'html') {
            return $dollars . '<?php echo ' . $this->htmlchars($code) . " ?>\n";
        }

        if ($format === 'cdata') {
            // quite complex for an "unescaped" section, isn't it?
            if ($this->getOutputMode() === PHPTAL::HTML5) {
                return $dollars . "<?php echo str_replace('</','<\\\\/', " . $this->stringify($code) . ") ?>\n";
            }

            if ($this->getOutputMode() === PHPTAL::XHTML) {
                // both XML and HMTL, because people will inevitably send it as text/html :(
                return $dollars . '<?php echo strtr(' . $this->stringify($code) . " ,array(']]>'=>']]]]><![CDATA[>','</'=>'<\\/')) ?>\n";
            }

            return $dollars . "<?php echo str_replace(']]>',']]]]><![CDATA[>', " . $this->stringify($code) . ") ?>\n";
        }
        return '';
    }

    /**
     * expects PHP code and returns PHP code that will generate escaped string
     * Optimizes case when PHP string is given.
     *
     * @param string $php
     *
     * @return string php code
     */
    public function htmlchars(string $php): string
    {
        // PHP strings can be escaped at compile time
        if (preg_match('/^\'((?:[^\'{]+|\\\\.)*)\'$/s', $php, $m)) {
            return "'" . htmlspecialchars(str_replace('\\\'', "'", $m[1]), ENT_QUOTES, $this->encoding) . "'";
        }
        return '\PhpTal\Helper::phptal_escape(' . $php . ', \'' . $this->encoding . '\')';
    }

    /**
     * allow proper printing of any object
     * (without escaping - for use with structure keyword)
     *
     * @param string $php
     *
     * @return string php code
     */
    public function stringify(string $php): string
    {
        // PHP strings don't need to be changed
        if (preg_match('/^\'(?>[^\'\\\\]+|\\\\.)*\'$|^\s*"(?>[^"\\\\]+|\\\\.)*"\s*$/s', $php)) {
            return $php;
        }
        return '\PhpTal\Helper::phptal_tostring(' . $php . ')';
    }
}
