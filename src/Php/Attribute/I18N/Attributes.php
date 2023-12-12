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
use PhpTal\Exception\ConfigurationException;
use PhpTal\Exception\ParserException;
use PhpTal\Exception\TemplateException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;
use PhpTal\Php\TalesInternal;
use ReflectionException;

/**
 *  i18n:attributes
 *
 * This attribute will allow us to translate attributes of HTML tags, such
 * as the alt attribute in the img tag. The i18n:attributes attribute
 * specifies a list of attributes to be translated with optional message
 * IDs? for each; if multiple attribute names are given, they must be
 * separated by semi-colons. Message IDs? used in this context must not
 * include whitespace.
 *
 * Note that the value of the particular attributes come either from the
 * HTML attribute value itself or from the data inserted by tal:attributes.
 *
 * If an attibute is to be both computed using tal:attributes and translated,
 * the translation service is passed the result of the TALES expression for
 * that attribute.
 *
 * An example:
 *
 *     <img src="http://foo.com/logo" alt="Visit us"
 *              tal:attributes="alt here/greeting"
 *              i18n:attributes="alt"
 *              />
 *
 *
 * In this example, let tal:attributes set the value of the alt attribute to
 * the text "Stop by for a visit!". This text will be passed to the
 * translation service, which uses the result of language negotiation to
 * translate "Stop by for a visit!" into the requested language. The example
 * text in the template, "Visit us", will simply be discarded.
 *
 * Another example, with explicit message IDs:
 *
 *   <img src="../icons/uparrow.png" alt="Up"
 *        i18n:attributes="src up-arrow-icon; alt up-arrow-alttext"
 *   >
 *
 * Here, the message ID up-arrow-icon will be used to generate the link to
 * an icon image file, and the message ID up-arrow-alttext will be used for
 * the "alt" text.
 *
 *
 *
 * @package PHPTAL
 */
class Attributes extends Attribute
{
    /**
     * Called before element printing.
     * @throws TemplateException
     * @throws ConfigurationException
     */
    public function before(CodeWriter $codewriter): void
    {
        // split attributes to translate
        foreach ($codewriter->splitExpression($this->expression) as $exp) {
            [$qname, $key] = $this->parseSetExpression($exp);

            // if the translation key is specified and not empty (but may be '0')
            if ($key !== null && strlen($key) > 0) {
                // we use it and replace the tag attribute with the result of the translation
                $code = $this->getTranslationCode($codewriter, $key);
            } else {
                $attr = $this->phpelement->getAttributeNode($qname);
                if (!$attr) {
                    throw new TemplateException(
                        "Unable to translate attribute $qname, because there is no translation key specified",
                        $this->phpelement->getSourceFile(),
                        $this->phpelement->getSourceLine()
                    );
                }

                if ($attr->getReplacedState() === Attr::NOT_REPLACED) {
                    $code = $this->getTranslationCode($codewriter, $attr->getValue());
                } elseif ($attr->getReplacedState() === Attr::VALUE_REPLACED && $attr->getOverwrittenVariableName()) {
                    // sadly variables won't be interpolated in this translation
                    $code = 'echo ' . $codewriter->escapeCode($codewriter->getTranslatorReference() . '->translate(' . $attr->getOverwrittenVariableName() . ', false)');
                } else {
                    throw new TemplateException(
                        "Unable to translate attribute $qname, because other TAL attributes are using it",
                        $this->phpelement->getSourceFile(),
                        $this->phpelement->getSourceLine()
                    );
                }
            }
            $this->phpelement->getOrCreateAttributeNode($qname)->overwriteValueWithCode($code);
        }
    }

    /**
     * Called after element printing.
     */
    public function after(CodeWriter $codewriter): void
    {
    }

    /**
     * @param string $key - unescaped string (not PHP code) for the key
     *
     * @throws ConfigurationException
     * @throws ParserException
     * @throws UnknownModifierException
     * @throws ReflectionException
     */
    private function getTranslationCode(CodeWriter $codewriter, string $key): string
    {
        $code = '';
        if (preg_match_all('/\$\{(.*?)\}/', $key, $m)) {
            array_shift($m);
            $m = array_shift($m);
            foreach ($m as $name) {
                $code .= "\n" . $codewriter->getTranslatorReference() . '->setVar(' . $codewriter->str($name) . ',' . TalesInternal::compileToPHPExpression($name) . ');'; // allow more complex TAL expressions
            }
            $code .= "\n";
        }

        // notice the false boolean which indicate that the html is escaped
        // elsewhere looks like an hack doesn't it ? :)
        $code .= 'echo ' . $codewriter->escapeCode($codewriter->getTranslatorReference() . '->translate(' . $codewriter->str($key) . ', false)');
        return $code;
    }
}
