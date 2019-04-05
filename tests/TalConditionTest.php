<?php
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

namespace Tests;

use Tests\Testhelper\CountableImpl;
use Tests\Testhelper\Event;

class TalConditionTest extends \Tests\Testcase\PhpTal
{
    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/tal-condition.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-condition.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testNot()
    {
        $tpl = $this->newPHPTAL('input/tal-condition.02.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-condition.02.html');
        $this->assertEquals($exp, $res);
    }

    public function testExists()
    {
        $tpl = $this->newPHPTAL('input/tal-condition.03.html');
        $tpl->somevar = true;
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-condition.03.html');
        $this->assertEquals($exp, $res);
    }

    public function testException()
    {
        $tpl = $this->newPHPTAL('input/tal-condition.04.html');
        $tpl->somevar = true;
        try {
            $tpl->execute();
        }
        catch (\Exception $e)
        {
        }
        $this->assertEquals(true, isset($e));
        // $exp = \Tests\Testhelper\Helper::normalize_html_file('output/tal-condition.04.html');
        // $this->assertEquals($exp, $res);
    }

    public function testTrueNonExistentVariable()
    {
        $tpl = $this->newPHPTAL()->setSource('<div tal:condition="true:doesntexist/nope"></div>');
        try {
            $this->assertEquals('', $tpl->execute());
        }
        catch(\Exception $e)
        {
            $this->fail($tpl->getCodePath());
        }
    }

    public function testFalsyValues()
    {
        $tpl = $this->newPHPTAL();
        $tpl->falsyValues = array(0,false,null,'0',"",array(),new CountableImpl());

        $tpl->setSource('<div tal:repeat="val falsyValues">
            val ${repeat/val/key}
            <tal:block tal:condition="val">true</tal:block>
            <tal:block tal:condition="falsyValues/${repeat/val/key}">true</tal:block>
            <tal:block tal:condition="not:falsyValues/${repeat/val/key}">false</tal:block>
            <tal:block tal:condition="not:val">false</tal:block>
            <tal:block tal:condition="not:true:val">false</tal:block>
        </div>');

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml("
        <div>val 0 false false false</div>
        <div>val 1 false false false</div>
        <div>val 2 false false false</div>
        <div>val 3 false false false</div>
        <div>val 4 false false false</div>
        <div>val 5 false false false</div>
        <div>val 6 false false false</div>
        "), \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }

    public function testTruthyValuesSimple()
    {
        $tpl = $this->newPHPTAL();
        $tpl->truthyValues = array(-1,0.00001,true,'null','00'," ",array(false),new CountableImpl(1));

        $tpl->setSource('<div tal:repeat="val truthyValues">
            val ${repeat/val/key}
            <tal:block tal:condition="val">true</tal:block>
            <tal:block tal:condition="true:val">true</tal:block>
            <tal:block tal:condition="not:val">false</tal:block>
        </div>');

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml("
        <div>val 0 true true</div>
        <div>val 1 true true</div>
        <div>val 2 true true</div>
        <div>val 3 true true</div>
        <div>val 4 true true</div>
        <div>val 5 true true</div>
        <div>val 6 true true</div>
        <div>val 7 true true</div>
        "), \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }

    public function testTruthyValuesComplex()
    {
        $tpl = $this->newPHPTAL();
        $tpl->truthyValues = array(-1,0.00001,true,'null','00'," ",array(false),new CountableImpl(1));

        $tpl->setSource('<div tal:repeat="val truthyValues">
            val ${repeat/val/key}
            <tal:block tal:condition="truthyValues/${repeat/val/key}">true</tal:block>
            <tal:block tal:condition="true:truthyValues/${repeat/val/key}">true</tal:block>
        </div>');

        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml("
        <div>val 0 true true</div>
        <div>val 1 true true</div>
        <div>val 2 true true</div>
        <div>val 3 true true</div>
        <div>val 4 true true</div>
        <div>val 5 true true</div>
        <div>val 6 true true</div>
        <div>val 7 true true</div>
        "), \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }

    public function testChainedFalse()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block tal:condition="foo | bar | baz | nothing">fail!</tal:block>');
        $res = $tpl->execute();
        $this->assertEquals($res, '');
    }

    public function testChainedTrue()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block tal:condition="foo | bar | baz | \'ok!\'">ok</tal:block>');
        $res = $tpl->execute();
        $this->assertEquals($res, 'ok');
    }

    public function testChainedShortCircuit()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<tal:block tal:condition="foo | \'ok!\' | bar | nothing">ok</tal:block>');
        $res = $tpl->execute();
        $this->assertEquals($res, 'ok');
    }

    public function testConditionCountable()
    {
        $event = new Event();
        $event->setArtists(new CountableImpl(0));

        $tal = $this->newPHPTAL();
        $tal->setSource('<div tal:condition="event/getArtists">
                            ${event/getArtists/0/makeDescription | \'fail\'}
                         </div>');

        $tal->set('event', $event);

        $this->assertEquals("",$tal->execute(), $tal->getCodePath());
    }
}
