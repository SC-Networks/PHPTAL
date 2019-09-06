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

use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\CountableImpl;
use Tests\Testhelper\Event;
use Tests\Testhelper\Helper;

class TalTestCaseConditionTest extends PhpTalTestCase
{
    public function testSimple(): void
    {
        $tpl = $this->newPHPTAL('input/tal-condition.01.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-condition.01.html');
        static::assertSame($exp, $res);
    }

    public function testNot(): void
    {
        $tpl = $this->newPHPTAL('input/tal-condition.02.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-condition.02.html');
        static::assertSame($exp, $res);
    }

    public function testExists(): void
    {
        $tpl = $this->newPHPTAL('input/tal-condition.03.html');
        $tpl->somevar = true;
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-condition.03.html');
        static::assertSame($exp, $res);
    }

    public function testException(): void
    {
        $tpl = $this->newPHPTAL('input/tal-condition.04.html');
        $tpl->somevar = true;
        $this->expectException(\Exception::class);
        $tpl->execute();
    }

    public function testTrueNonExistentVariable(): void
    {
        $tpl = $this->newPHPTAL()->setSource('<div tal:condition="true:doesntexist/nope"></div>');
        static::assertSame('', $tpl->execute());
    }

    public function testFalsyValues(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->falsyValues = [0, false, null, '0', "", [], new CountableImpl()];

        $tpl->setSource('<div tal:repeat="val falsyValues">
            val ${repeat/val/key}
            <tal:block tal:condition="val">true</tal:block>
            <tal:block tal:condition="falsyValues/${repeat/val/key}">true</tal:block>
            <tal:block tal:condition="not:falsyValues/${repeat/val/key}">false</tal:block>
            <tal:block tal:condition="not:val">false</tal:block>
            <tal:block tal:condition="not:true:val">false</tal:block>
        </div>');

        static::assertSame(Helper::normalizeHtml("
        <div>val 0 false false false</div>
        <div>val 1 false false false</div>
        <div>val 2 false false false</div>
        <div>val 3 false false false</div>
        <div>val 4 false false false</div>
        <div>val 5 false false false</div>
        <div>val 6 false false false</div>
        "), Helper::normalizeHtml($tpl->execute()));
    }

    public function testTruthyValuesSimple(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->truthyValues = [-1, 0.00001, true, 'null', '00', " ", [false], new CountableImpl(1)];

        $tpl->setSource('<div tal:repeat="val truthyValues">
            val ${repeat/val/key}
            <tal:block tal:condition="val">true</tal:block>
            <tal:block tal:condition="true:val">true</tal:block>
            <tal:block tal:condition="not:val">false</tal:block>
        </div>');

        static::assertSame(Helper::normalizeHtml("
        <div>val 0 true true</div>
        <div>val 1 true true</div>
        <div>val 2 true true</div>
        <div>val 3 true true</div>
        <div>val 4 true true</div>
        <div>val 5 true true</div>
        <div>val 6 true true</div>
        <div>val 7 true true</div>
        "), Helper::normalizeHtml($tpl->execute()));
    }

    public function testTruthyValuesComplex(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->truthyValues = [-1, 0.00001, true, 'null', '00', " ", [false], new CountableImpl(1)];

        $tpl->setSource('<div tal:repeat="val truthyValues">
            val ${repeat/val/key}
            <tal:block tal:condition="truthyValues/${repeat/val/key}">true</tal:block>
            <tal:block tal:condition="true:truthyValues/${repeat/val/key}">true</tal:block>
        </div>');

        static::assertSame(Helper::normalizeHtml("
        <div>val 0 true true</div>
        <div>val 1 true true</div>
        <div>val 2 true true</div>
        <div>val 3 true true</div>
        <div>val 4 true true</div>
        <div>val 5 true true</div>
        <div>val 6 true true</div>
        <div>val 7 true true</div>
        "), Helper::normalizeHtml($tpl->execute()));
    }

    public function testChainedFalse(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block tal:condition="foo | bar | baz | nothing">fail!</tal:block>');
        $res = $tpl->execute();
        static::assertSame($res, '');
    }

    public function testChainedTrue(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block tal:condition="foo | bar | baz | \'ok!\'">ok</tal:block>');
        $res = $tpl->execute();
        static::assertSame($res, 'ok');
    }

    public function testChainedShortCircuit(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block tal:condition="foo | \'ok!\' | bar | nothing">ok</tal:block>');
        $res = $tpl->execute();
        static::assertSame($res, 'ok');
    }

    public function testConditionCountable(): void
    {
        $event = new Event();
        $event->setArtists(new CountableImpl(0));

        $tal = $this->newPHPTAL();
        $tal->setSource('<div tal:condition="event/getArtists">
                            ${event/getArtists/0/makeDescription | \'fail\'}
                         </div>');

        $tal->set('event', $event);

        static::assertSame("", $tal->execute(), $tal->getCodePath());
    }
}
