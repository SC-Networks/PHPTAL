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

namespace PhpTal\Php\Attribute\METAL;

use PhpTal\Dom\Attr;
use PhpTal\Dom\Element;
use PhpTal\Dom\Node;
use PhpTal\Exception\PhpTalException;
use PhpTal\Php\Attribute;
use PhpTal\Php\CodeWriter;
use PhpTal\TalNamespace\Builtin;

/**
 *  METAL Specification 1.0
 *
 *      argument ::= Name
 *
 * Example:
 *
 *       <table metal:use-macro="here/doc1/macros/sidebar">
 *        <tr><th>Links</th></tr>
 *        <tr><td metal:fill-slot="links">
 *          <a href="http://www.goodplace.com">Good Place</a><br>
 *          <a href="http://www.badplace.com">Bad Place</a><br>
 *          <a href="http://www.otherplace.com">Other Place</a>
 *        </td></tr>
 *      </table>
 *
 * PHPTAL:
 *
 * 1. evaluate slots
 *
 * <?php ob_start(); ? >
 * <td>
 *   <a href="http://www.goodplace.com">Good Place</a><br>
 *   <a href="http://www.badplace.com">Bad Place</a><br>
 *   <a href="http://www.otherplace.com">Other Place</a>
 * </td>
 * <?php $tpl->slots->links = ob_get_contents(); ob_end_clean(); ? >
 *
 * 2. call the macro (here not supported)
 *
 * <?php echo phptal_macro($tpl, 'master_page.html/macros/sidebar'); ? >
 *
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class FillSlot extends Attribute
{
    public const CALLBACK_THRESHOLD = 10000;

    /**
     * @var int
     */
    private static $uid = 0;

    /**
     * @var string|null
     */
    private $function_name;

    /**
     * Called before element printing.
     *
     * @param CodeWriter $codewriter
     */
    public function before(CodeWriter $codewriter): void
    {
        if ($this->shouldUseCallback()) {
            $function_base_name = 'slot_' . preg_replace('/[^a-z0-9]/', '_', $this->expression) . '_' . (self::$uid++);
            $codewriter->doFunction($function_base_name, '\PhpTal\PHPTAL $_thistpl, \PhpTal\PHPTAL $tpl');
            $this->function_name = $codewriter->getFunctionPrefix() . $function_base_name;

            $codewriter->doSetVar('$ctx', '$tpl->getContext()');
            $codewriter->doInitTranslator();
        } else {
            $codewriter->pushCode('ob_start()');
            $this->function_name = null;
        }
    }

    /**
     * Called after element printing.
     *
     * @param CodeWriter $codewriter
     *
     * @throws PhpTalException
     */
    public function after(CodeWriter $codewriter): void
    {
        if ($this->function_name !== null) {
            $codewriter->doEnd();
            $codewriter->pushCode(
                '$ctx->fillSlotCallback(' . $codewriter->str($this->expression) . ', '
                . $codewriter->str($this->function_name) . ', $_thistpl, clone $tpl)'
            );
        } else {
            $codewriter->pushCode('$ctx->fillSlot(' . $codewriter->str($this->expression) . ', ob_get_clean())');
        }
    }

    /**
     * inspects contents of the element to decide whether callback makes sense
     */
    private function shouldUseCallback(): bool
    {
        // since callback is slightly slower than buffering,
        // use callback only for content that is large to offset speed loss by memory savings
        return $this->estimateNumberOfBytesOutput($this->phpelement, false) > self::CALLBACK_THRESHOLD;
    }

    /**
     * @param Element $element
     * @param bool $is_nested_in_repeat true if any parent element has tal:repeat
     *
     * @return int
     */
    private function estimateNumberOfBytesOutput(Element $element, bool $is_nested_in_repeat): int
    {
        // macros don't output anything on their own
        if ($element->hasAttributeNS(Builtin::NS_METAL, 'define-macro')) {
            return 0;
        }

        $estimated_bytes = 2 * (3 + strlen($element->getQualifiedName()));

        foreach ($element->getAttributeNodes() as $attr) {
            $estimated_bytes += 4 + strlen($attr->getQualifiedName());
            if ($attr->getReplacedState() === Attr::NOT_REPLACED) {
                $estimated_bytes += strlen($attr->getValueEscaped()); // this is shoddy for replaced attributes
            }
        }

        $has_repeat_attr = $element->hasAttributeNS(Builtin::NS_TAL, 'repeat');
        $isRepeating = $has_repeat_attr || $is_nested_in_repeat;

        if ($element->hasAttributeNS(Builtin::NS_TAL, 'content') ||
            $element->hasAttributeNS(Builtin::NS_TAL, 'replace')) {
            // assume that output in loops is shorter (e.g. table rows) than outside (main content)
            $estimated_bytes += $isRepeating ? 500 : 2000;
        } else {
            foreach ($element->childNodes as $node) {
                if ($node instanceof Element) {
                    $estimated_bytes += $this->estimateNumberOfBytesOutput(
                        $node,
                        $isRepeating
                    );
                } else {
                    /** @var Node $node */
                    $estimated_bytes += strlen($node->getValueEscaped());
                }
            }
        }

        if ($element->hasAttributeNS(Builtin::NS_METAL, 'use-macro')) {
            $estimated_bytes += $isRepeating ? 500 : 2000;
        }

        if ($element->hasAttributeNS(Builtin::NS_TAL, 'condition')) {
            $estimated_bytes /= 2; // naively assuming 50% chance, that works well with if/else pattern
        }

        if ($element->hasAttributeNS(Builtin::NS_TAL, 'repeat')) {
            // assume people don't write big nested loops
            $estimated_bytes *= $is_nested_in_repeat ? 5 : 10;
        }

        return (int) $estimated_bytes;
    }
}
