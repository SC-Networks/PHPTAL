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

namespace PhpTal\Php\Attribute\I18N;

use PhpTal\Dom\Attr;
use PhpTal\Dom\Element;
use PhpTal\Dom\Node;
use PhpTal\Dom\Text;
use PhpTal\Exception\ConfigurationException;
use PhpTal\Exception\ParserException;
use PhpTal\Exception\PhpNotAllowedException;
use PhpTal\Exception\PhpTalException;
use PhpTal\Exception\TemplateException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\Php\Attribute;
use PhpTal\Php\Attribute\TAL\Content;
use PhpTal\Php\CodeWriter;
use PhpTal\Php\TalesChainExecutor;
use PhpTal\TalNamespace\Builtin;
use ReflectionException;

/**
 * ZPTInternationalizationSupport
 *
 * i18n:translate
 *
 * This attribute is used to mark units of text for translation. If this
 * attribute is specified with an empty string as the value, the message ID
 * is computed from the content of the element bearing this attribute.
 * Otherwise, the value of the element gives the message ID.
 *
 *
 * @package PHPTAL
 */
class Translate extends Content
{
    /**
     * @param CodeWriter $codewriter
     *
     * @return void
     *
     * @throws TemplateException
     * @throws ConfigurationException
     * @throws ParserException
     * @throws PhpNotAllowedException
     * @throws PhpTalException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    public function before(CodeWriter $codewriter): void
    {
        $escape = true;
        $this->echoType = Attribute::ECHO_TEXT;
        if (preg_match('/^(text|structure)(?:\s+(.*)|\s*$)/', $this->expression, $m)) {
            if ($m[1] === 'structure') {
                $escape = false;
                $this->echoType = Attribute::ECHO_STRUCTURE;
            }
            $this->expression = $m[2] ?? '';
        }

        $this->prepareNames($codewriter, $this->phpelement);

        // if no expression is given, the content of the node is used as
        // a translation key
        if (trim($this->expression) === '') {
            $key = $this->getTranslationKey($this->phpelement, !$escape, $codewriter->getEncoding());
            $key = trim(preg_replace('/\s+/sm' . ($codewriter->getEncoding() === 'UTF-8' ? 'u' : ''), ' ', $key));
            if (trim($key) === '') {
                throw new TemplateException(
                    'Empty translation key',
                    $this->phpelement->getSourceFile(),
                    $this->phpelement->getSourceLine()
                );
            }
            $code = $codewriter->str($key);
        } else {
            $code = $codewriter->evaluateExpression($this->expression);
            if (is_array($code)) {
                $this->generateChainedContent($codewriter, $code);
                return;
            }

            $code = $codewriter->evaluateExpression($this->expression);
        }

        $codewriter->pushCode(
            'echo ' . $codewriter->getTranslatorReference() . '->translate(' . $code . ',' . ($escape ? 'true' : 'false') . ');'
        );
    }

    /**
     * @param CodeWriter $codewriter
     * @return void
     */
    public function after(CodeWriter $codewriter): void
    {
    }

    /**
     * @param TalesChainExecutor $executor
     * @param string $expression
     * @param bool $islast
     *
     * @return void
     * @throws ConfigurationException
     * @throws PhpTalException
     */
    public function talesChainPart(TalesChainExecutor $executor, string $expression, bool $islast): void
    {
        $codewriter = $executor->getCodeWriter();

        $escape = Attribute::ECHO_STRUCTURE !== $this->echoType;
        $expression = $codewriter->getTranslatorReference() . "->translate($expression, " . ($escape ? 'true' : 'false') . ')';
        if (!$islast) {
            $var = $codewriter->createTempVariable();
            $executor->doIf('!\PhpTal\Helper::phptal_isempty(' . $var . ' = ' . $expression . ')');
            $codewriter->pushCode("echo $var");
            $codewriter->recycleTempVariable($var);
        } else {
            $executor->doElse();
            $codewriter->pushCode("echo $expression");
        }
    }

    /**
     * @param Node&Element $tag
     * @param bool $preserve_tags
     * @param string $encoding
     *
     * @return string
     */
    private function getTranslationKey(Node $tag, bool $preserve_tags, string $encoding): string
    {
        $result = '';
        foreach ($tag->childNodes as $child) {
            if ($child instanceof Text) {
                if ($preserve_tags) {
                    $result .= $child->getValueEscaped();
                } else {
                    $result .= $child->getValue();
                }
            } elseif ($child instanceof Element) {
                $attr = $child->getAttributeNodeNS(Builtin::NS_I18N, 'name');
                if ($attr) {
                    $result .= '${' . $attr->getValue() . '}';
                } elseif ($preserve_tags) {
                    $result .= '<' . $child->getQualifiedName();
                    foreach ($child->getAttributeNodes() as $attr) {
                        if ($attr->getReplacedState() === Attr::HIDDEN) {
                            continue;
                        }

                        $result .= ' ' . $attr->getQualifiedName() . '="' . $attr->getValueEscaped() . '"';
                    }
                    $result .= '>' . $this->getTranslationKey($child, $preserve_tags, $encoding) .
                        '</' . $child->getQualifiedName() . '>';
                } else {
                    $result .= $this->getTranslationKey($child, $preserve_tags, $encoding);
                }
            }
        }
        return $result;
    }

    /**
     * @param CodeWriter $codewriter
     * @param Node&Element $tag
     *
     * @return void
     *
     * @throws PhpTalException
     * @throws TemplateException
     */
    private function prepareNames(CodeWriter $codewriter, Node $tag): void
    {
        foreach ($tag->childNodes as $child) {
            if ($child instanceof Element) {
                if ($child->hasAttributeNS(Builtin::NS_I18N, 'name')) {
                    $child->generateCode($codewriter);
                } else {
                    $this->prepareNames($codewriter, $child);
                }
            }
        }
    }
}
