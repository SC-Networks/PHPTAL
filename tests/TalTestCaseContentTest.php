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

use PhpTal\Exception\VariableNotFoundException;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\DummyToStringObject;
use Tests\Testhelper\Helper;

class TalTestCaseContentTest extends PhpTalTestCase
{
    public function testSimple(): void
    {
        $tpl = $this->newPHPTAL('input/tal-content.01.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-content.01.html');
        static::assertSame($exp, $res);
    }

    public function testVar(): void
    {
        $tpl = $this->newPHPTAL('input/tal-content.02.html');
        $tpl->content = 'my content';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-content.02.html');
        static::assertSame($exp, $res);
    }

    public function testStructure(): void
    {
        $tpl = $this->newPHPTAL('input/tal-content.03.html');
        $tpl->content = '<foo><bar/></foo>';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-content.03.html');
        static::assertSame($exp, $res);
    }

    public function testNothing(): void
    {
        $tpl = $this->newPHPTAL('input/tal-content.04.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-content.04.html');
        static::assertSame($exp, $res);
    }

    public function testDefault(): void
    {
        $tpl = $this->newPHPTAL('input/tal-content.05.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-content.05.html');
        static::assertSame($exp, $res);
    }

    public function testChain(): void
    {
        $tpl = $this->newPHPTAL('input/tal-content.06.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-content.06.html');
        static::assertSame($exp, $res);
    }

    public function testEmpty(): void
    {
        $src = '
<root>
<span tal:content="nullv | falsev | emptystrv | zerov | default">default</span>
<span tal:content="nullv | falsev | emptystrv | default">default</span>
</root>
';
        $exp = '
<root>
<span>0</span>
<span>default</span>
</root>
';
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->nullv = null;
        $tpl->falsev = false;
        $tpl->emptystrv = '';
        $tpl->zerov = 0;
        $res = $tpl->execute();
        static::assertSame(Helper::normalizeHtml($exp), Helper::normalizeHtml($res));
    }

    public function testObjectEcho(): void
    {
        $foo = new DummyToStringObject('foo value');
        $src = <<<EOT
<root tal:content="foo"/>
EOT;
        $exp = <<<EOT
<root>foo value</root>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->foo = $foo;
        $res = $tpl->execute();
        static::assertSame($res, $exp);
    }

    public function testObjectEchoStructure(): void
    {
        $foo = new DummyToStringObject('foo value');
        $src = <<<EOT
<root tal:content="structure foo"/>
EOT;
        $exp = <<<EOT
<root>foo value</root>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src);
        $tpl->foo = $foo;
        $res = $tpl->execute();
        static::assertSame($res, $exp);
    }

    public function testErrorsThrow(): void
    {
        $this->expectException(VariableNotFoundException::class);
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="erroridontexist"/>');
        $tpl->execute();
    }

    public function testErrorsThrow2(): never
    {
        $this->expectException(VariableNotFoundException::class);
        $this->markTestSkipped("tal:define and tal:attributes rely on chains not throwing");//FIXME

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="erroridontexist2 | erroridontexist2"/>');
        $tpl->execute();
    }

    public function testErrorsThrow3(): never
    {
        $this->expectException(VariableNotFoundException::class);
        $this->markTestSkipped("tal:define and tal:attributes rely on chains not throwing");//FIXME

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:replace="erroridontexist3 | erroridontexist3"/>');
        $tpl->execute();
    }

    public function testErrorsThrow4(): never
    {
        $this->expectException(VariableNotFoundException::class);
        $this->markTestSkipped("tal:define and tal:attributes rely on chains not throwing");//FIXME

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:condition="erroridontexist4 | erroridontexist4"/>');
        $tpl->execute();
    }

    public function testErrorsSilenced(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="erroridontexist | nothing"/>');
        static::assertSame('<p></p>', $tpl->execute());
    }

    public function testZeroIsNotEmpty(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->zero = '0';
        $tpl->setSource('<p tal:content="zero | erroridontexist"/>');
        static::assertSame('<p>0</p>', $tpl->execute());
    }

    public function testFalseLast(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->one_row = ['RESPONSIBLE_OFFICE' => 'responsible_office1'];
        $tpl->setSource(
            '<span tal:define="resp_office offices/${one_row/RESPONSIBLE_OFFICE} | false">${resp_office}</span>'
        );

        static::assertSame('<span>0</span>', $tpl->execute());
    }
}
