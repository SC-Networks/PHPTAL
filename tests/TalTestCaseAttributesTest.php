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

use PhpTal\PHPTAL;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class TalTestCaseAttributesTest extends PhpTalTestCase
{
    public function testSimple(): void
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.01.html');
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.01.html');
        static::assertSame($exp, $res);
    }

    public function testWithContent(): void
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.02.html');
        $tpl->spanClass = 'dummy';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.02.html');
        static::assertSame($exp, $res);
    }

    public function testMultiples(): void
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.03.html');
        $tpl->spanClass = 'dummy';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.03.html');
        static::assertSame($exp, $res);
    }

    public function testChain(): void
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.04.html');
        $tpl->spanClass = 'dummy';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.04.html');
        static::assertSame($exp, $res);
    }

    public function testMultipleChains(): void
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.05.html');
        $tpl->spanClass = 'dummy';
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.05.html');
        static::assertSame($exp, $res);
    }

    public function testEncoding(): void
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.06.html');
        $tpl->href = "http://www.test.com/?foo=bar&buz=biz&<thisissomething";
        $tpl->title = 'bla bla <blabla>';
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.06.html');
        static::assertSame($exp, $res);
    }

    public function testZeroValues(): void
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.07.html');
        $tpl->href1 = 0;
        $tpl->href2 = 0;
        $tpl->href3 = 0;
        $res = Helper::normalizeHtml($tpl->execute());
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.07.html');
        static::assertSame($exp, $res);
    }

    public function testEmpty(): void
    {
        $src = <<<EOT
<span class="&quot;'default" tal:attributes="class nullv | falsev | emptystrv | zerov | default"></span>
EOT;
        $exp = <<<EOT
<span class="0"></span>
EOT;
        $tpl = $this->newPHPTAL();
        $tpl->setSource($src, __FILE__);
        $tpl->nullv = null;
        $tpl->falsev = false;
        $tpl->emptystrv = '';
        $tpl->zerov = 0;
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testSingleQuote(): void
    {
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.08.html');
        $tpl = $this->newPHPTAL('input/tal-attributes.08.html');
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testStructure(): void
    {
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.09.html');
        $tpl = $this->newPHPTAL('input/tal-attributes.09.html');
        $tpl->value = "return confirm('hel<lo');";
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testChainedStructure(): void
    {
        $exp = Helper::normalizeHtmlFile('output/tal-attributes.10.html');
        $tpl = $this->newPHPTAL('input/tal-attributes.10.html');
        $tpl->value1 = false;
        $tpl->value2 = "return confirm('hel<lo');";
        $res = $tpl->execute();
        static::assertSame($exp, $res);
    }

    public function testNothingValue(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:attributes="title missing | nothing"></p>');
        $res = $tpl->execute();
        static::assertSame($res, '<p></p>');
    }

    public function testNULLValueNoAlternative(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:attributes="title NULL"></p>');
        $res = $tpl->execute();
        static::assertSame('<p></p>', $res);
    }

    public function testEmptyValue(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(
            '<p tal:attributes="title missing | \'\'"></p><p tal:attributes="title missing | php:\'\'"></p>'
        );
        $res = $tpl->execute();
        static::assertSame('<p title=""></p><p title=""></p>', $res);
    }

    public function testSemicolon(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource(
            '<div><p tal:content="\'\\\'a;b;;c;;;d\'" tal:attributes="style \'color:red;; font-weight:bold;;;;\'; title php:\'\\\'test;;test;;;;test\'"></p></div>'
        );
        $res = $tpl->execute();
        static::assertSame(
            $res,
            '<div><p style="color:red; font-weight:bold;;" title="&#039;test;test;;test">&#039;a;b;;c;;;d</p></div>'
        );
    }

    public function testBoolean(): void
    {
        $booleanAttrs = [
            'checked',
            'disabled',
            'autoplay',
            'async',
            'autofocus',
            'controls',
            'default',
            'defer',
            'formnovalidate',
            'hidden',
            'ismap',
            'itemscope',
            'loop',
            'multiple',
            'novalidate',
            'open',
            'pubdate',
            'readonly',
            'required',
            'reversed',
            'scoped',
            'seamless',
            'selected'
        ];
        foreach ($booleanAttrs as $name) {
            // XHTML
            $tpl = $this->newPHPTAL();
            $tpl->setOutputMode(PHPTAL::XHTML);
            $tpl->setSource('<p ' . $name . '="123" tal:attributes="' . $name . ' attrval"></p>');
            $tpl->attrval = true;
            $res = $tpl->execute();
            static::assertSame('<p ' . $name . '="' . $name . '"></p>', $res);
            $tpl->attrval = false;
            $res = $tpl->execute();
            static::assertSame('<p></p>', $res);
            // HTML5
            $tpl = $this->newPHPTAL();
            $tpl->setOutputMode(PHPTAL::HTML5);
            $tpl->setSource('<p ' . $name . '="123" tal:attributes="' . $name . ' attrval"></p>');
            $tpl->attrval = true;
            $res = $tpl->execute();
            static::assertSame('<p ' . $name . '></p>', $res);
            $tpl->attrval = false;
            $res = $tpl->execute();
            static::assertSame('<p></p>', $res);
        }
    }
}
