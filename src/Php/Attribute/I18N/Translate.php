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

namespace PhpTal\Php\Attribute\I18N;

use PhpTal\TalNamespace\Builtin;

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
class Translate extends \PhpTal\Php\Attribute\TAL\Content
{
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $escape = true;
        $this->_echoType = \PhpTal\Php\Attribute::ECHO_TEXT;
        if (preg_match('/^(text|structure)(?:\s+(.*)|\s*$)/', $this->expression, $m)) {
            if ($m[1]=='structure') { $escape=false; $this->_echoType = \PhpTal\Php\Attribute::ECHO_STRUCTURE; }
            $this->expression = isset($m[2])?$m[2]:'';
        }

        $this->_prepareNames($codewriter, $this->phpelement);

        // if no expression is given, the content of the node is used as
        // a translation key
        if (strlen(trim($this->expression)) == 0) {
            $key = $this->_getTranslationKey($this->phpelement, !$escape, $codewriter->getEncoding());
            $key = trim(preg_replace('/\s+/sm'.($codewriter->getEncoding()=='UTF-8'?'u':''), ' ', $key));
            if ('' === trim($key)) {
                throw new \PhpTal\Exception\TemplateException("Empty translation key",
                            $this->phpelement->getSourceFile(), $this->phpelement->getSourceLine());
            }
            $code = $codewriter->str($key);
        } else {
            $code = $codewriter->evaluateExpression($this->expression);
            if (is_array($code))
                return $this->generateChainedContent($codewriter, $code);

            $code = $codewriter->evaluateExpression($this->expression);
        }

        $codewriter->pushCode('echo '.$codewriter->getTranslatorReference().'->translate('.$code.','.($escape ? 'true':'false').');');
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
    }

    public function talesChainPart(\PhpTal\Php\TalesChainExecutor $executor, $exp, $islast)
    {
        $codewriter = $executor->getCodeWriter();

        $escape = !($this->_echoType == \PhpTal\Php\Attribute::ECHO_STRUCTURE);
        $exp = $codewriter->getTranslatorReference()."->translate($exp, " . ($escape ? 'true':'false') . ')';
        if (!$islast) {
            $var = $codewriter->createTempVariable();
            $executor->doIf('!\PhpTal\Helper::phptal_isempty('.$var.' = '.$exp.')');
            $codewriter->pushCode("echo $var");
            $codewriter->recycleTempVariable($var);
        } else {
            $executor->doElse();
            $codewriter->pushCode("echo $exp");
        }
    }

    private function _getTranslationKey(\PhpTal\Dom\Node $tag, $preserve_tags, $encoding)
    {
        $result = '';
        foreach ($tag->childNodes as $child) {
            if ($child instanceof \PhpTal\Dom\Text) {
                if ($preserve_tags) {
                    $result .= $child->getValueEscaped();
                } else {
                    $result .= $child->getValue($encoding);
                }
            } elseif ($child instanceof \PhpTal\Dom\Element) {
                if ($attr = $child->getAttributeNodeNS(Builtin::NS_I18N, 'name')) {
                    $result .= '${' . $attr->getValue() . '}';
                } else {

                    if ($preserve_tags) {
                        $result .= '<'.$child->getQualifiedName();
                        foreach ($child->getAttributeNodes() as $attr) {
                            if ($attr->getReplacedState() === \PhpTal\Dom\Attr::HIDDEN) continue;

                            $result .= ' '.$attr->getQualifiedName().'="'.$attr->getValueEscaped().'"';
                        }
                        $result .= '>'.$this->_getTranslationKey($child, $preserve_tags, $encoding) . '</'.$child->getQualifiedName().'>';
                    } else {
                        $result .= $this->_getTranslationKey($child, $preserve_tags, $encoding);
                    }
                }
            }
        }
        return $result;
    }

    private function _prepareNames(\PhpTal\Php\CodeWriter $codewriter, \PhpTal\Dom\Node $tag)
    {
        foreach ($tag->childNodes as $child) {
            if ($child instanceof \PhpTal\Dom\Element) {
                if ($child->hasAttributeNS(Builtin::NS_I18N, 'name')) {
                    $child->generateCode($codewriter);
                } else {
                    $this->_prepareNames($codewriter, $child);
                }
            }
        }
    }
}
