<?php
declare(strict_types=1);

/**
 * PHPTAL templating engine
 *
 * Originally developed by Laurent Bedubourg and Kornel Lesiński
 *
 * @category HTML
 * @package  PHPTAL
 * @author   Laurent Bedubourg <lbedubourg@motion-twin.com>
 * @author   Kornel Lesiński <kornel@aardvarkmedia.co.uk>
 * @author   See contributors list @ github
 * @license  http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 * @link     http://phptal.org/
 * @link     https://github.com/SC-Networks/PHPTAL
 */

namespace Tests;

use PhpTal\Exception\MacroMissingException;
use PhpTal\Exception\ParserException;
use PhpTal\Exception\PhpTalException;
use stdClass;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class MetalMacroTest extends PhpTalTestCase
{

    public function testSimple(): void
    {
        $tpl = $this->newPHPTAL('input/metal-macro.01.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/metal-macro.01.html');
        static::assertSame($exp, $res);
    }

    public function testExternalMacro(): void
    {
        $tpl = $this->newPHPTAL('input/metal-macro.02.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/metal-macro.02.html');
        static::assertSame($exp, $res);
    }

    public function testBlock(): void
    {
        $tpl = $this->newPHPTAL('input/metal-macro.03.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/metal-macro.03.html');
        static::assertSame($exp, $res);
    }

    public function testMacroInsideMacro(): void
    {
        $tpl = $this->newPHPTAL('input/metal-macro.04.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/metal-macro.04.html');
        static::assertSame($exp, $res);
    }

    public function testEvaluatedMacroName(): void
    {
        $call = new stdClass();
        $call->first = 1;
        $call->second = 2;

        $tpl = $this->newPHPTAL('input/metal-macro.05.html');
        $tpl->call = $call;

        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/metal-macro.05.html');
        static::assertSame($exp, $res);
    }

    public function testEvaluatedMacroNameTalesPHP(): void
    {
        static::markTestSkipped('chaining does not work anymore at the moment.');
        $call = new stdClass();
        $call->first = 1;
        $call->second = 2;

        $tpl = $this->newPHPTAL('input/metal-macro.06.html');
        $tpl->call = $call;

        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/metal-macro.06.html');
        static::assertSame($exp, $res);
    }

    public function testInheritedMacroSlots(): void
    {
        $tpl = $this->newPHPTAL('input/metal-macro.07.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/metal-macro.07.html');
        static::assertSame($exp, $res);
    }

    public function testBadMacroNameException(): void
    {
        $tpl = $this->newPHPTAL('input/metal-macro.08.html');
        $this->expectException(ParserException::class);
        $tpl->execute();
    }

    public function testExternalMacroMissingException(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(sprintf('<tal:block metal:use-macro="%sinput/metal-macro.07.html/this-macro-doesnt-exist"/>',
            TAL_TEST_FILES_DIR
        ));
        $this->expectException(MacroMissingException::class);
        $tpl->execute();
    }

    public function testMacroMissingException(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block metal:use-macro="this-macro-doesnt-exist"/>');
        $this->expectException(MacroMissingException::class);
        $tpl->execute();
    }

    public function testMixedCallerDefiner(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->defined_later_var = 'defined_later';
        $tpl->ok_var = '??'; // fallback in case test fails
        $tpl->setSource(sprintf('<tal:block metal:use-macro="%sinput/metal-macro.09.html/defined_earlier" />',
            TAL_TEST_FILES_DIR
        ));
        $res = $tpl->execute();
        static::assertSame('Call OK OK', trim(preg_replace('/\s+/', ' ', $res)));
    }

    public function testMacroRedefinitionIsGraceful(): void
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

    public function testSameMacroCanBeDefinedInDifferentTemplates(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block metal:define-macro=" foo ">1</tal:block>');
        $tpl->execute();

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block metal:define-macro=" foo ">2</tal:block>');
        $tpl->execute();
    }

    public function testExternalTemplateThrowsError(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(sprintf('<phptal:block metal:use-macro="%sinput/metal-macro.10.html/throwerr"/>',
            TAL_TEST_FILES_DIR
        ));
        $this->expectException(ParserException::class);
        $tpl->execute();
    }

    public function testOnErrorCapturesErorrInExternalMacro(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(sprintf('<phptal:block tal:on-error="string:ok"
        metal:use-macro="%sinput/metal-macro.10.html/throwerr"/>', TAL_TEST_FILES_DIR
        ));

        static::assertSame('ok', $tpl->execute());
    }

    public function testGlobalDefineInExternalMacro(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(sprintf('<metal:block>
            <phptal:block tal:define="global test string:bad"
            metal:use-macro="%sinput/metal-macro.11.html/redefine"/>
            ${test}
            </metal:block>', TAL_TEST_FILES_DIR));

        static::assertSame('ok', trim($tpl->execute()));
    }
}
