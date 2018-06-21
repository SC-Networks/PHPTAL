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

namespace Tests;

use PhpTal\Exception\MacroMissingException;
use PhpTal\Exception\ParserException;
use PhpTal\Exception\PhpTalException;

class MetalMacroTest extends \Tests\Testcase\PhpTal
{

    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/metal-macro.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-macro.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testExternalMacro()
    {
        $tpl = $this->newPHPTAL('input/metal-macro.02.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-macro.02.html');
        $this->assertEquals($exp, $res);
    }

    public function testBlock()
    {
        $tpl = $this->newPHPTAL('input/metal-macro.03.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-macro.03.html');
        $this->assertEquals($exp, $res);
    }

    public function testMacroInsideMacro()
    {
        $tpl = $this->newPHPTAL('input/metal-macro.04.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-macro.04.html');
        $this->assertEquals($exp, $res);
    }

    public function testEvaluatedMacroName()
    {
        $call = new \stdClass();
        $call->first = 1;
        $call->second = 2;

        $tpl = $this->newPHPTAL('input/metal-macro.05.html');
        $tpl->call = $call;

        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-macro.05.html');
        $this->assertEquals($exp, $res);
    }

    public function testEvaluatedMacroNameTalesPHP()
    {
        static::markTestSkipped('chaining does not work anymore at the moment.');
        $call = new \stdClass();
        $call->first = 1;
        $call->second = 2;

        $tpl = $this->newPHPTAL('input/metal-macro.06.html');
        $tpl->call = $call;

        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-macro.06.html');
        $this->assertEquals($exp, $res);
    }

    public function testInheritedMacroSlots()
    {
        $tpl = $this->newPHPTAL('input/metal-macro.07.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-macro.07.html');
        $this->assertEquals($exp, $res);
    }

    public function testBadMacroNameException()
    {
        $tpl = $this->newPHPTAL('input/metal-macro.08.html');
        $this->expectException(ParserException::class);
        $tpl->execute();
    }

    public function testExternalMacroMissingException()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block metal:use-macro="../input/metal-macro.07.html/this-macro-doesnt-exist"/>');
        $this->expectException(MacroMissingException::class);
        $tpl->execute();
    }

    public function testMacroMissingException()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block metal:use-macro="this-macro-doesnt-exist"/>');
        $this->expectException(MacroMissingException::class);
        $tpl->execute();
    }

    public function testMixedCallerDefiner()
    {
        $tpl = $this->newPHPTAL();
        $tpl->defined_later_var = 'defined_later';
        $tpl->ok_var = '??'; // fallback in case test fails
        $tpl->setSource('<tal:block metal:use-macro="../input/metal-macro.09.html/defined_earlier" />');
        $res = $tpl->execute();
        $this->assertEquals('Call OK OK', trim(preg_replace('/\s+/', ' ', $res)));
    }

    public function testMacroRedefinitionIsGraceful()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(
            '<p>
          <metal:block define-macro=" foo " />
              <a metal:define-macro="foo">bar</a>
         </p>');
        $this->expectException(PhpTalException::class);
        $tpl->execute();
    }

    public function testSameMacroCanBeDefinedInDifferentTemplates()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block metal:define-macro=" foo ">1</tal:block>');
        $tpl->execute();

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block metal:define-macro=" foo ">2</tal:block>');
        $tpl->execute();
    }

    public function testExternalTemplateThrowsError()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<phptal:block metal:use-macro="../input/metal-macro.10.html/throwerr"/>');
        $this->expectException(ParserException::class);
        $tpl->execute();
    }

    public function testOnErrorCapturesErorrInExternalMacro()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<phptal:block tal:on-error="string:ok"
        metal:use-macro="../input/metal-macro.10.html/throwerr"/>');

        $this->assertEquals('ok', $tpl->execute());
    }

    public function testGlobalDefineInExternalMacro()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<metal:block>
            <phptal:block tal:define="global test string:bad"
            metal:use-macro="../input/metal-macro.11.html/redefine"/>
            ${test}
            </metal:block>');

        $this->assertEquals('ok', trim($tpl->execute()));
    }
}
