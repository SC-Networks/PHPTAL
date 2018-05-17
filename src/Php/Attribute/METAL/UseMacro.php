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

namespace PhpTal\Php\Attribute\METAL;

/**
 * METAL Specification 1.0
 *
 *      argument ::= expression
 *
 * Example:
 *
 *      <hr />
 *      <p metal:use-macro="here/master_page/macros/copyright">
 *      <hr />
 *
 * PHPTAL: (here not supported)
 *
 *      <?php echo phptal_macro( $tpl, 'master_page.html/macros/copyright'); ? >
 *
 *
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class UseMacro extends \PhpTal\Php\Attribute
{
    static $ALLOWED_ATTRIBUTES = array(
        'fill-slot'=>'http://xml.zope.org/namespaces/metal',
        'define-macro'=>'http://xml.zope.org/namespaces/metal',
        'define'=>'http://xml.zope.org/namespaces/tal',
    );

    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $this->pushSlots($codewriter);

        foreach ($this->phpelement->childNodes as $child) {
            $this->generateFillSlots($codewriter, $child);
        }

        $macroname = strtr($this->expression, '-', '_');

	// throw error if attempting to define and use macro at same time
	// [should perhaps be a TemplateException? but I don't know how to set that up...]
	if ($defineAttr = $this->phpelement->getAttributeNodeNS(
		'http://xml.zope.org/namespaces/metal', 'define-macro')) {
		if ($defineAttr->getValue() == $macroname) 
            		throw new \PhpTal\Exception\TemplateException("Cannot simultaneously define and use macro '$macroname'",
                		$this->phpelement->getSourceFile(), $this->phpelement->getSourceLine());			
	}

        // local macro (no filename specified) and non dynamic macro name
        // can be called directly if it's a known function (just generated or seen in previous compilation)
        if (preg_match('/^[a-z0-9_]+$/i', $macroname) && $codewriter->functionExists($macroname)) {
            $code = $codewriter->getFunctionPrefix() . $macroname . '($_thistpl, $tpl)';
            $codewriter->pushCode($code);
        }
        // external macro or ${macroname}, use PHPTAL at runtime to resolve it
        else {
            $code = $codewriter->interpolateTalesVarsInString($this->expression);
            $codewriter->pushCode('$tpl->executeMacroOfTemplate('.$code.', $_thistpl)');
        }

        $this->popSlots($codewriter);
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
    }

    /**
     * reset template slots on each macro call ?
     *
     * NOTE: defining a macro and using another macro on the same tag
     * means inheriting from the used macro, thus slots are shared, it
     * is a little tricky to understand but very natural to use.
     *
     * For example, we may have a main design.html containing our main
     * website presentation with some slots (menu, content, etc...) then
     * we may define a member.html macro which use the design.html macro
     * for the general layout, fill the menu slot and let caller templates
     * fill the parent content slot without interfering.
     */
    private function pushSlots(\PhpTal\Php\CodeWriter $codewriter)
    {
        if (!$this->phpelement->hasAttributeNS('http://xml.zope.org/namespaces/metal', 'define-macro')) {
            $codewriter->pushCode('$ctx->pushSlots()');
        }
    }

    /**
     * generate code that pops macro slots
     * (restore slots if not inherited macro)
     */
    private function popSlots(\PhpTal\Php\CodeWriter $codewriter)
    {
        if (!$this->phpelement->hasAttributeNS('http://xml.zope.org/namespaces/metal', 'define-macro')) {
            $codewriter->pushCode('$ctx->popSlots()');
        }
    }

    /**
     * recursively generates code for slots
     */
    private function generateFillSlots(\PhpTal\Php\CodeWriter $codewriter, \PhpTal\Dom\Node $phpelement)
    {
        if (false == ($phpelement instanceof \PhpTal\Dom\Element)) {
            return;
        }

        // if the tag contains one of the allowed attribute, we generate it
        foreach (self::$ALLOWED_ATTRIBUTES as $qname => $uri) {
            if ($phpelement->hasAttributeNS($uri, $qname)) {
                $phpelement->generateCode($codewriter);
                return;
            }
        }

        foreach ($phpelement->childNodes as $child) {
            $this->generateFillSlots($codewriter, $child);
        }
    }
}
