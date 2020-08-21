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

use DOMDocument;
use SimpleXMLElement;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;
use Tests\Testhelper\MyIterableWithSize;
use Tests\Testhelper\LogIteratorCalls;
use Tests\Testhelper\MyArrayObj;
use Tests\Testhelper\MyIterable;
use Tests\Testhelper\MyIterableThrowsOnSize;
use Tests\Testhelper\SizeCalledException;

class TalTestCaseRepeatTest extends PhpTalTestCase
{
    public function testArrayRepeat(): void
    {
        $tpl = $this->newPHPTAL('input/tal-repeat.01.html');
        $tpl->array = range(0, 4);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-repeat.01.html');
        static::assertSame($exp, $res);
    }

    public function testOddEventAndFriends(): void
    {
        $tpl = $this->newPHPTAL('input/tal-repeat.02.html');
        $tpl->array = range(0, 2);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-repeat.02.html');
        static::assertSame($exp, $res);
    }

    public function testIterableUsage(): void
    {
        $tpl = $this->newPHPTAL('input/tal-repeat.03.html');
        $tpl->result = new MyIterableWithSize(4);
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-repeat.03.html');
        static::assertSame($exp, $res);
    }

    public function testArrayObject(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(
            '<div><p tal:repeat="a aobj" tal:content="a"></p><p tal:repeat="a aobj" tal:content="a"></p></div>'
        );
        $tpl->aobj = new MyArrayObj([1, 2, 3]);

        static::assertSame('<div><p>1</p><p>2</p><p>3</p><p>1</p><p>2</p><p>3</p></div>', $tpl->execute());
    }

    public function testArrayObjectOneElement(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(
            '<div><p tal:repeat="a aobj" tal:content="a"></p><p tal:repeat="a aobj" tal:content="a"></p></div>'
        );
        $tpl->aobj = new MyArrayObj([1]);

        static::assertSame('<div><p>1</p><p>1</p></div>', $tpl->execute());
    }

    public function testArrayObjectZeroElements(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(
            '<div><p tal:repeat="a aobj" tal:content="a"></p><p tal:repeat="a aobj" tal:content="a"/></div>'
        );
        $tpl->aobj = new MyArrayObj([]);

        static::assertSame('<div></div>', $tpl->execute());
    }

    public function testArrayObjectAggregated(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<div><p tal:repeat="a aobj">${a}${repeat/a/length}</p></div>');
        $tpl->aobj = new MyArrayObj(new MyArrayObj(["1", "2", "3", null]));

        static::assertSame('<div><p>14</p><p>24</p><p>34</p><p>4</p></div>', $tpl->execute());
    }

    public function testArrayObjectNested(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<div><p tal:repeat="a aobj">${a}<b tal:repeat="b aobj" tal:content="b"/></p></div>');
        $tpl->aobj = new MyArrayObj(["1", "2"]);

        static::assertSame('<div><p>1<b>1</b><b>2</b></p><p>2<b>1</b><b>2</b></p></div>', $tpl->execute());
    }

    public function testHashKey(): void
    {
        $tpl = $this->newPHPTAL('input/tal-repeat.04.html');
        $tpl->result = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3];
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-repeat.04.html');
        static::assertSame($exp, $res);
    }

    public function testRepeatAttributesWithPhp(): void
    {
        $tpl = $this->newPHPTAL('input/tal-repeat.05.html');
        $tpl->data = [1, 2, 3];
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-repeat.05.html');
        static::assertSame($exp, $res);
    }


    public function testRepeatAttributesWithMacroPhp(): void
    {
        $tpl = $this->newPHPTAL('input/tal-repeat.06.html');
        $tpl->data = [1, 2, 3];
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-repeat.06.html');
        static::assertSame($exp, $res);
    }


    public function testPhpMode(): void
    {
        $tpl = $this->newPHPTAL('input/tal-repeat.07.html');
        $tpl->result = ['a' => 0, 'b' => 1, 'c' => 2, 'd' => 3];
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-repeat.07.html');
        static::assertSame($exp, $res);
    }

    public function testTraversableRepeat(): void
    {
        static::markTestSkipped('this condition works with php only. maybe add comparism-operators to tal:condition?');
        $doc = new DOMDocument();
        $doc->loadXML('<a><b/><c/><d/><e/><f/><g/></a>');

        $tpl = $this->newPHPTAL();
        $tpl->setSource(
            '<tal:block tal:repeat="node nodes"><tal:block tal:condition="php:repeat.node.index==4">(len=${repeat/node/length})</tal:block>${repeat/node/key}${node/tagName}</tal:block>'
        );
        $tpl->nodes = $doc->getElementsByTagName('*');

        static::assertSame('0a1b2c3d(len=7)4e5f6g', $tpl->execute());

    }

    public function testLetter(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<span tal:omit-tag="" tal:repeat="item items" tal:content="repeat/item/letter"/>');
        $tpl->items = range(0, 32);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = 'abcdefghijklmnopqrstuvwxyzaaabacadaeafag';
        static::assertSame($exp, $res);
    }

    public function testRoman(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<span tal:omit-tag="" tal:repeat="item items" tal:content="string:${repeat/item/roman},"/>');
        $tpl->items = range(0, 16);
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = 'i,ii,iii,iv,v,vi,vii,viii,ix,x,xi,xii,xiii,xiv,xv,xvi,xvii,';
        static::assertSame($exp, $res);
    }

    public function testGrouping(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('
            <div tal:omit-tag="" tal:repeat="item items">
                <h1 tal:condition="repeat/item/first" tal:content="item"></h1>
                <p tal:condition="not: repeat/item/first" tal:content="item"></p>
                <hr tal:condition="repeat/item/last" />
            </div>'
        );
        $tpl->items = ['apple', 'apple', 'orange', 'orange', 'orange', 'pear', 'kiwi', 'kiwi'];
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtml('
            <h1>apple</h1>
            <p>apple</p>
            <hr/>
            <h1>orange</h1>
            <p>orange</p>
            <p>orange</p>
            <hr/>
            <h1>pear</h1>
            <hr/>
            <h1>kiwi</h1>
            <p>kiwi</p>
            <hr/>'
        );

        static::assertSame($exp, $res);
    }

    public function testGroupingPath(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('
            <div tal:omit-tag="" tal:repeat="item items">
                <h1 tal:condition="repeat/item/first/type" tal:content="item/type"></h1>
                <p tal:content="item/name"></p>
                <hr tal:condition="repeat/item/last/type" />
            </div>'
        );
        $tpl->items = [
            ['type' => 'car', 'name' => 'bmw'],
            ['type' => 'car', 'name' => 'audi'],
            ['type' => 'plane', 'name' => 'boeing'],
            ['type' => 'bike', 'name' => 'suzuki'],
            ['type' => 'bike', 'name' => 'honda'],
        ];
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtml('
            <h1>car</h1>
            <p>bmw</p>
            <p>audi</p>
            <hr/>
            <h1>plane</h1>
            <p>boeing</p>
            <hr/>
            <h1>bike</h1>
            <p>suzuki</p>
            <p>honda</p>
            <hr/>'
        );

        static::assertSame($exp, $res);
    }

    public function testSimpleXML(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource("<tal:block tal:repeat='s sxml'><b tal:content='structure s' />\n</tal:block>");
        $tpl->sxml = new SimpleXMLElement("<x><y>test</y><y attr=\"test\"><z>test</z></y><y/></x>");
        static::assertSame(
            "<b><y>test</y></b>\n<b><y attr=\"test\"><z>test</z></y></b>\n<b><y/></b>\n",
            $tpl->execute()
        );
    }


    public function testSameCallsAsForeach(): void
    {
        $foreach = new LogIteratorCalls([1, 2, 3]);

        foreach ($foreach as $k => $x) {
            // noop
        }

        $controller = new LogIteratorCalls([1, 2, 3]);

        $phptal = $this->newPHPTAL();
        $phptal->iter = $controller;
        $phptal->setSource('<tal:block tal:repeat="x iter" />');
        $phptal->execute();

        static::assertSame($foreach->log, $controller->log);
    }

    public function testCountIsLazy(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->i = new MyIterableThrowsOnSize(10);
        $tpl->setSource('<tal:block tal:repeat="i i">${repeat/i/start}[${repeat/i/key}]${repeat/i/end}</tal:block>');
        static::assertSame("1[0]00[1]00[2]00[3]00[4]00[5]00[6]00[7]00[8]00[9]1", $tpl->execute());

        try {
            $tpl->i = new MyIterableThrowsOnSize(10);
            $tpl->setSource('<tal:block tal:repeat="i i">aaaaa${repeat/i/length}aaaaa</tal:block>');
            echo $tpl->execute();
            $this->fail("Expected SizeCalledException");
        } catch (SizeCalledException $e) {
        }
    }

    public function testReset(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->iter = $i = new LogIteratorCalls(new MyIterableThrowsOnSize(10));

        $tpl->setSource(
            '<tal:block tal:repeat="i iter">${repeat/i/start}[${repeat/i/key}]${repeat/i/end}</tal:block><tal:block tal:repeat="i iter">${repeat/i/start}[${repeat/i/key}]${repeat/i/end}</tal:block>'
        );

        $res = $tpl->execute();
        static::assertSame(
            "1[0]00[1]00[2]00[3]00[4]00[5]00[6]00[7]00[8]00[9]11[0]00[1]00[2]00[3]00[4]00[5]00[6]00[7]00[8]00[9]1",
            $res,
            $tpl->getCodePath()
        );
        static::assertMatchesRegularExpression("/rewind.*rewind/s", $i->log);
        static::assertSame(
            "1[0]00[1]00[2]00[3]00[4]00[5]00[6]00[7]00[8]00[9]11[0]00[1]00[2]00[3]00[4]00[5]00[6]00[7]00[8]00[9]1",
            $tpl->execute()
        );
    }

    public function testFakedLength(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->iter = new MyIterable(10);
        $tpl->setSource(
            '<tal:block tal:repeat="i iter">${repeat/i/start}[${repeat/i/key}/${repeat/i/length}]${repeat/i/end}</tal:block>'
        );
        static::assertSame(
            "1[0/]00[1/]00[2/]00[3/]00[4/]00[5/]00[6/]00[7/]00[8/]00[9/10]1",
            $tpl->execute(),
            $tpl->getCodePath()
        );
    }

    public function testPushesContext(): void
    {
        $phptal = $this->newPHPTAL();
        $phptal->setSource('
        <x>
        original=${user}
        <y tal:define="user \'defined\'">
        defined=${user}
        <z tal:repeat="user users">
        repeat=${user}
        <z tal:repeat="user users2">
        repeat2=${user}
        </z>
        repeat=${user}
        </z>
        defined=${user}
        </y>
        original=${user}</x>
        ');

        $phptal->user = 'original';
        $phptal->users = ['repeat'];
        $phptal->users2 = ['repeat2'];

        static::assertSame(
            Helper::normalizeHtml(
                '<x> original=original <y> defined=defined <z> repeat=repeat <z> repeat2=repeat2 </z> repeat=repeat </z> defined=defined </y> original=original</x>'
            ),
            Helper::normalizeHtml($phptal->execute()));
    }
}
