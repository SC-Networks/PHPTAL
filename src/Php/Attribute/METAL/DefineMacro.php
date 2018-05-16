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
 * @version  SVN: $Id$
 * @link     http://phptal.org/
 */

namespace PhpTal\Php\Attribute\METAL;

/**
 * METAL Specification 1.0
 *
 *      argument ::= Name
 *
 * Example:
 *
 *      <p metal:define-macro="copyright">
 *      Copyright 2001, <em>Foobar</em> Inc.
 *      </p>
 *
 * PHPTAL:
 *
 *      <?php function XXX_macro_copyright($tpl) { ? >
 *        <p>
 *        Copyright 2001, <em>Foobar</em> Inc.
 *        </p>
 *      <?php } ? >
 *
 * @package PHPTAL
 * @author Laurent Bedubourg <lbedubourg@motion-twin.com>
 */
class DefineMacro extends \PhpTal\Php\Attribute
{
    public function before(\PhpTal\Php\CodeWriter $codewriter)
    {
        $macroname = strtr(trim($this->expression), '-', '_');
        if (!preg_match('/^[a-z0-9_]+$/i', $macroname)) {
            throw new \PhpTal\Exception\ParserException('Bad macro name "'.$macroname.'"',
                $this->phpelement->getSourceFile(), $this->phpelement->getSourceLine());
        }

        if ($codewriter->functionExists($macroname)) {
            throw new \PhpTal\Exception\TemplateException("Macro $macroname is defined twice",
                $this->phpelement->getSourceFile(), $this->phpelement->getSourceLine());
        }

        $codewriter->doFunction($macroname, '\PhpTal\PHPTAL $_thistpl, \PhpTal\PHPTAL $tpl');
        $codewriter->doSetVar('$tpl', 'clone $tpl');
        $codewriter->doSetVar('$ctx', '$tpl->getContext()');
        $codewriter->doInitTranslator();
        $codewriter->doXmlDeclaration(true);
        $codewriter->doDoctype(true);
    }

    public function after(\PhpTal\Php\CodeWriter $codewriter)
    {
        $codewriter->doEnd('function');
    }
}
