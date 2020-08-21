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

use PhpTal\Exception\TemplateException;
use PhpTal\Php\Attribute\TAL\Define;
use PhpTal\Php\TalesInternal;
use PhpTal\PHPTAL;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\DummyDefinePhpNode;
use Tests\Testhelper\Helper;

class TalTestCaseDefineTest extends PhpTalTestCase
{

    public function tearDown(): void
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    public function testExpressionParser(): void
    {
        $att = new Define(new DummyDefinePhpNode(), 'a b');

        list($defineScope, $defineVar, $expression) = $att->parseExpression('local a_234z b');
        static::assertSame('local', $defineScope);
        static::assertSame('a_234z', $defineVar);
        static::assertSame('b', $expression);

        list($defineScope, $defineVar, $expression) = $att->parseExpression('global a_234z b');
        static::assertSame('global', $defineScope);
        static::assertSame('a_234z', $defineVar);
        static::assertSame('b', $expression);

        list($defineScope, $defineVar, $expression) = $att->parseExpression('a_234Z b');
        static::assertFalse($defineScope);
        static::assertSame('a_234Z', $defineVar);
        static::assertSame('b', $expression);

        list($defineScope, $defineVar, $expression) = $att->parseExpression('a');
        static::assertFalse($defineScope);
        static::assertSame('a', $defineVar);
        static::assertNull($expression);

        list($defineScope, $defineVar, $expression) = $att->parseExpression('global a string: foo; bar; baz');
        static::assertSame('global', $defineScope);
        static::assertSame('a', $defineVar);
        static::assertSame('string: foo; bar; baz', $expression);


        list($defineScope, $defineVar, $expression) = $att->parseExpression('foo this != other');
        static::assertFalse($defineScope);
        static::assertSame('foo', $defineVar);
        static::assertSame('this != other', $expression);

        list($defineScope, $defineVar, $expression) = $att->parseExpression('x exists: a | not: b | path: c | 128');
        static::assertFalse($defineScope);
        static::assertSame('x', $defineVar);
        static::assertSame('exists: a | not: b | path: c | 128', $expression);
    }

    public function testBuffered(): void
    {
        $tpl = $this->newPHPTAL('input/tal-define.02.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.02.html');
        static::assertSame($exp, $res);
    }

    public function testMultiChained(): void
    {
        $tpl = $this->newPHPTAL('input/tal-define.03.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.03.html');
        static::assertSame($exp, $res);
    }

    public function testDefineZero(): void
    {
        $tpl = $this->newPHPTAL('input/tal-define.04.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.04.html');
        static::assertSame($exp, $res);
    }

    public function testDefineInMacro(): void
    {
        $tpl = $this->newPHPTAL('input/tal-define.06.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.06.html');
        static::assertSame($exp, $res);
    }

    public function testDefineDoNotStealOutput(): void
    {
        $tpl = $this->newPHPTAL('input/tal-define.07.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.07.html');
        static::assertSame($exp, $res);
    }

    public function testDefineWithRepeatAndContent(): void
    {
        TalesInternal::setFunctionWhitelist(['range']);
        $tpl = $this->newPHPTAL('input/tal-define.08.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.08.html');
        static::assertSame($exp, $res);
    }

    public function testDefineWithUseMacro(): void
    {
        $tpl = $this->newPHPTAL('input/tal-define.09.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.09.html');
        static::assertSame($exp, $res);
    }

    public function testDefineAndPrint(): void
    {
        $tpl = $this->newPHPTAL('input/tal-define.10.html');
        $tpl->fname = 'Roger';
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.10.html');
        static::assertSame($exp, $res);
    }

    public function testDefineContent(): void
    {
        $tpl = $this->newPHPTAL('input/tal-define.11.html');
        $tpl->setOutputMode(PHPTAL::XML);
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.11.html');
        static::assertSame($exp, $res);
    }

    public function testDefineAndAttributes(): void
    {
        TalesInternal::setFunctionWhitelist(['true']);
        $tpl = $this->newPHPTAL('input/tal-define.12.html');
        $tpl->setOutputMode(PHPTAL::XML);
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-define.12.html');
        static::assertSame($exp, $res);
    }

    public function testDefineGlobal(): void
    {
        $exp = Helper::normalizeHtmlFile('output/tal-define.13.html');
        $tpl = $this->newPHPTAL('input/tal-define.13.html');
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame($exp, $res);
    }

    public function testDefineAlter(): void
    {
        $exp = Helper::normalizeHtmlFile('output/tal-define.14.html');
        $tpl = $this->newPHPTAL('input/tal-define.14.html');
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame($exp, $res);
    }

    public function testDefineSemicolon(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:define="one \';;\'; two string:;;;;; three php:\';;;;;;\'">${one}-${two}-${three}</p>');
        static::assertSame('<p>;-;;-;;;</p>', $tpl->execute());
    }

    public function testEmpty(): void
    {
        TalesInternal::setFunctionWhitelist(['count', 'book']);
        $tal = $this->newPHPTAL();
        $tal->setSource(
            '<div class="blank_bg" tal:define="book relative/book" tal:condition="php: count(book)>0"></div>'
        );
        $tal->relative = ['book' => [1]];

        static::assertSame($tal->execute(), '<div class="blank_bg"></div>');
    }

    public function testGlobalDefineEmptySpan(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<div>
           <span tal:define="global x \'ok\'" />
           ${x}
        </div>
        ');
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame(Helper::normalizeHtml('<div> ok </div>'), $res);
    }

    public function testGlobalDefineEmptySpan2(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<div>
           <span tal:define="global x \'ok\'" tal:comment="ignoreme" />
           ${x}
        </div>
        ');
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame(Helper::normalizeHtml('<div> ok </div>'), $res);
    }


    public function testGlobalDefineNonEmptySpan(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setOutputMode(PHPTAL::XML);

        $tpl->setSource('<div>
           <span tal:define="global x \'ok\'" class="foo" />
           ${x}
        </div>
        ');
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame(Helper::normalizeHtml('<div> <span class="foo"/> ok </div>'), $res);
    }

    public function testGlobalDefineNonEmptySpan2(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setOutputMode(PHPTAL::XML);

        $tpl->setSource('<div>
           <span tal:define="global x \'ok\'" tal:attributes="class \'foo\'" />
           ${x}
        </div>
        ');
        $res = Helper::normalizeHtml($tpl->execute());
        static::assertSame(Helper::normalizeHtml('<div> <span class="foo"/> ok </div>'), $res);
    }

    public function testDefineTALESInterpolated(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->varvar = 'ok';
        $tpl->varname = 'varvar';
        $tpl->setSource('<div tal:define="test ${varname}">${test}</div>');
        static::assertSame('<div>ok</div>', $tpl->execute());
    }

    public function testDefinePHPInterpolated(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->varvar = 'ok';
        $tpl->varname = 'varvar';
        $tpl->setSource('<div tal:define="test ${varname}">${test}</div>');
        static::assertSame('<div>ok</div>', $tpl->execute());
    }

    public function testRedefineSelf(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->label = 'label var';
        $tpl->fail = 'not an array';
        $tpl->setSource('<tal:block tal:define="label fail/label|label" tal:replace="structure label"/>');

        static::assertSame('label var', $tpl->execute());
    }

    public function testRedefineSelf2(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->label = 'label var';
        $tpl->fail = 'not an array';
        $tpl->setSource('<tal:block tal:define="label fail/label|label|somethingelse" tal:replace="structure label"/>');

        static::assertSame('label var', $tpl->execute());
    }

    public function testRejectsInvalidExpression(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<x tal:define="global foo | default"/>');
        $this->expectException(TemplateException::class);
        $tpl->execute();
    }

    public function testHasRealContent(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<y
        phptal:debug="">

        <x
        tal:define="global foo bar | default"
        >
        test
        </x>
        </y>
        ');
        $tpl->execute();
    }

    public function testHasRealCDATAContent(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<script tal:define="global foo bar | default"><![CDATA[ x ]]></script>');
        $tpl->execute();
    }


    public function testDefineAndAttributesOnSameElement(): void
    {
        TalesInternal::setFunctionWhitelist(['row']);
        $tpl = $this->newPHPTAL();
        $tpl->team = 'zzz';
        $tpl->row = 'zzz';
        $tpl->event_name = 'zzz';
        $tpl->setSource('<tal:block tal:condition="php: isset(row.$team.$event_name)">
                        <td tal:define="event row/$team/$event_name" tal:attributes="style \'THIS DOESNT WORK\'">
                           ${event/player/surname}
                       </td>
                   </tal:block>');
        $tpl->execute();
    }
}
