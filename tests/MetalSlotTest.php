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

use PhpTal\Php\Attribute\METAL\FillSlot;
use PhpTal\Php\TalesInternal;
use Tests\Testhelper\DummyTranslator;

class MetalSlotTest extends \Tests\Testcase\PhpTal
{
    public function tearDown()
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/metal-slot.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-slot.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testVariableSlotName()
    {
        $tpl = $this->newPHPTAL()->setSource('<div>
          <div metal:define-macro="test">
            <div metal:define-slot="alt${varernative}">
               This is my alternative text which is shown when alternative slot is not filled
            </div>
          </div>

          <div metal:use-macro="test">
            <div metal:fill-slot="alternative">
               I don\'t want the alternative
            </div>
          </div>

          <div metal:use-macro="test">
             I want the alternative
          </div>
        </div>
        ');

        $tpl->varernative = 'ernative';

        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-slot.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testPreservesContext()
    {
        $tpl = $this->newPHPTAL('input/metal-slot.05.html');
        $tpl->var = "top";
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('top=top<div>inusemacro=inusemacro<div>inmacro=inmacro<div>infillslot=infillslot</div>/inmacro=inmacro</div>/inusemacro=inusemacro</div>/top=top'),
                            \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()), $tpl->getCodePath());
    }

    public function testPreservesTopmostContext()
    {
        $tpl = $this->newPHPTAL();
        $tpl->var = "topmost";
        $tpl->setSource('
            <div metal:define-macro="m">
                <div tal:define="var string:invalid">
                    <span metal:define-slot="s">empty slot</span>
                </div>
            </div>

            <div metal:use-macro="m">
                <div metal:fill-slot="s">var = ${var}</div>
            </div>
        ');
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<div><div><div>var = topmost</div></div></div>'), \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()), $tpl->getCodePath());
    }

    public function testRecursiveFillSimple()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('
            <div>
              <div metal:define-macro="test1">
                test1 macro value:<span metal:define-slot="value">a value should go here</span>
              </div>

              <div metal:define-macro="test2">
                test2 macro value:<span metal:define-slot="value">a value should go here</span>
              </div>

              <div metal:use-macro="test1" class="calls test1 macro">
                <div metal:fill-slot="value" class="filling value for test1">
                  <div metal:use-macro="test2" class="calls test2 macro">
                    <span metal:fill-slot="value" class="filling value for test2">foo bar baz</span>
                  </div>
                </div>
              </div>
            </div>
            ');

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<div><div>test1 macro value:<div class="filling value for test1">
        <div>test2 macro value:<span class="filling value for test2">foo bar baz</span></div></div></div></div>'),
                            \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()), $tpl->getCodePath());
    }

    public function testRecursiveFill()
    {
        $tpl = $this->newPHPTAL('input/metal-slot.02.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-slot.02.html');
        $this->assertEquals($exp, $res, $tpl->getCodePath());
    }

    public function testRecursiveUnFill()
    {
        $this->markTestSkipped("known bug"); // FIXME

        $tpl = $this->newPHPTAL()->setSource('<div>
          <div metal:define-macro="test1">
            <span metal:define-slot="val1"/>
          </div>

          <div metal:define-macro="test2">
            <span metal:define-slot="val2">OK</span>
          </div>

          <div metal:use-macro="test1">
            <div metal:fill-slot="val1"  tal:omit-tag="">
              <div metal:use-macro="test2"/>
              <nothing tal:comment="just to make it use callback code path" tal:repeat="x php:array()"><x tal:repeat="x php:array()" tal:content="x"></x></nothing>
            </div>
            <div metal:fill-slot="val2">ERROR</div>
          </div>

        </div>

        ');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $this->assertEquals('<div><div><div><span>OK</span></div></div></div>', $res, $tpl->getCodePath());
    }



    public function testBlock()
    {
        $tpl = $this->newPHPTAL('input/metal-slot.03.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-slot.03.html');
        $this->assertEquals($exp, $res);
    }

    public function testFillAndCondition()
    {
        $tpl = $this->newPHPTAL('input/metal-slot.04.html');
        $tpl->fillit = true;
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/metal-slot.04.html');
        $this->assertEquals($exp, $res);
    }

    public function testFillWithi18n()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('
        <div metal:use-macro="m1"><p metal:fill-slot="test-slot"><span i18n:translate="">translatemetoo</span></p></div>
        <div metal:define-macro="m1">
            <p metal:define-slot="test-slot" i18n:attributes="title" title="translateme">test</p>
        </div>
        ');

        $tr = new DummyTranslator();
        $tr->setTranslation("translateme", "translatedyou");
        $tr->setTranslation("translatemetoo", "translatedyouaswell");
        $tpl->setTranslator($tr);

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<div><p><span>translatedyouaswell</span></p></div>'), \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()), $tpl->getCodePath());
    }

    /**
     * this is violation of TAL specification, but needs to work for backwards compatibility
     */
    public function testFillPreservedAcrossCalls()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block metal:fill-slot="foo">foocontent</tal:block>');
        $tpl->execute();
        $tpl->setSource('<tal:block metal:define-slot="foo">FAIL</tal:block>');

        $this->assertEquals('foocontent', $tpl->execute());
    }

    public function testUsesCallbackForLargeSlots()
    {
        TalesInternal::setFunctionWhitelist(['range']);

        $tpl = $this->newPHPTAL();
        $tpl->setSource('
        <x metal:define-macro="foo"><y metal:define-slot="s"/></x>

        <f metal:use-macro="foo"><s metal:fill-slot="s">
            <loop tal:repeat="n php:range(1,5)">
                <inner tal:repeat="y php:range(1,5)">
                    <a title="stuff lots of stuff stuff lots of stuff stuff lots of stuff stuff lots of stuff lots of stuff stuff lots of stuff">stuff</a>
                    <a title="stuff lots of stuff stuff lots of stuff stuff lots of stuff stuff lots of stuff lots of stuff stuff lots of stuff">stuff</a>
                    <a title="stuff lots of stuff stuff lots of stuff stuff lots of stuff stuff lots of stuff lots of stuff stuff lots of stuff">stuff</a>
                </inner>
            </loop>
        </s></f>
        ');

        $res = $tpl->execute();
        $this->assertGreaterThan(FillSlot::CALLBACK_THRESHOLD, strlen($res));

        $tpl_php_source = file_get_contents($tpl->getCodePath());

        $this->assertNotContains("fillSlot(", $tpl_php_source);
        $this->assertContains("fillSlotCallback(", $tpl_php_source);
    }

    public function testUsesBufferForSmallSlots()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('
        <x metal:define-macro="foo"><y metal:define-slot="s"/></x>

        <f metal:use-macro="foo"><s metal:fill-slot="s">
            stuff lots of stuff stuff lots of stuff stuff lots of stuff stuff lots of stuff stuff lots of stuff stuff lots of stuff
        </s></f>
        ');

        $tpl->execute();

        $tpl_php_source = file_get_contents($tpl->getCodePath());

        $this->assertNotContains("fillSlotCallback(", $tpl_php_source);
        $this->assertContains("fillSlot(", $tpl_php_source);
    }

    public function testSlotBug()
    {
        $tpl = $this->newPHPTAL()->setSource(<<<HTML
        <div>
         <tal:block metal:define-macro="subpage">
           <tal:block metal:use-macro="page">
               <tal:block metal:fill-slot="valuebis">
                   <div>OK subpage filled page/valuebis</div>
               </tal:block>
           </tal:block>
         </tal:block>

         <tal:block metal:define-macro="page">
           page/value:<span metal:define-slot="value">FAIL unfilled page/value</span>
           page/valuebis:<span metal:define-slot="valuebis">FAIL unfilled page/valuebis</span>
         </tal:block>

         <tal:block metal:use-macro="subpage">
           <tal:block metal:fill-slot="valuebis">FAIL unused invalid subpage/valuebis</tal:block>
           <tal:block metal:fill-slot="value">
               <div>OK toplevel-filled page/value</div>
           </tal:block>
         </tal:block>
        </div>
HTML
);

        $this->assertEquals(
            \Tests\Testhelper\Helper::normalizeHtml('<div>page/value:<div>OK toplevel-filled page/value</div>page/valuebis:<div>OK subpage filled page/valuebis</div></div>'),
            \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()), $tpl->getCodePath());
    }

    public function testNestedSlots()
    {
        $tpl = $this->newPHPTAL()->setSource('
        <tal:block metal:define-macro="fieldset">
		<fieldset>
			<legend><tal:block metal:define-slot="legend" /></legend>
			<tal:block metal:define-slot="content" />
		</fieldset>
        </tal:block>

        <form>
		<tal:block metal:use-macro="fieldset">
			<tal:block metal:fill-slot="legend">First Level</tal:block>
			<tal:block metal:fill-slot="content">
				<tal:block metal:use-macro="fieldset">
					<tal:block metal:fill-slot="legend">Second Level</tal:block>
					<tal:block metal:fill-slot="content">
						<label>Question</label><input type="text" name="question" />
					</tal:block>
				</tal:block>
				<input type="submit" value="Send" />
			</tal:block>
		</tal:block>
        </form>
        ');

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('
        <form>
		<fieldset>
			<legend>First Level</legend>
			<fieldset>
				<legend>Second Level</legend>
				<label>Question</label><input type="text" name="question" />
			</fieldset>
			<input type="submit" value="Send" />
		</fieldset>
        </form>'),
        \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }

    public function testResetDefault()
    {
        $tpl = $this->newPHPTAL()->setSource('
        <!--! definition of macro with a slot -->
        <p metal:define-macro="the-macro">
          The macro : <tt metal:define-slot="the-slot">the slot</tt>
        </p>

        <!--! call macro with default slot -->
        <tal:block metal:use-macro="the-macro" />

        <!--! call macro and fill the slot -->
        <tal:block metal:use-macro="the-macro">
          <tt metal:fill-slot="the-slot">something else</tt>
        </tal:block>

        <!--! call macro with default slot : this FAIL -->
        <tal:block metal:use-macro="the-macro" />');

        $res = $tpl->execute();

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<p>
            The macro : <tt>the slot</tt>
          </p>
        <p>
            The macro : <tt>something else</tt>
          </p>
        <p>
            The macro : <tt>the slot</tt>
          </p>
        '),\Tests\Testhelper\Helper::normalizeHtml($res));
    }

    public function testTrickyName()
    {
        $tricky = "\\x&apos;\\\\&apos;&quot;\t\r\n\\";
        $res = $this->newPHPTAL()->setSource("
            <x metal:define-macro='t'>
            <tal:block metal:define-slot='$tricky'>slot</tal:block>
            </x>
            <y metal:use-macro='t'>
            <tal:block metal:fill-slot='$tricky'>filled</tal:block>
            </y>
        ")->execute();
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<x>filled</x>'), \Tests\Testhelper\Helper::normalizeHtml($res));
    }
}


