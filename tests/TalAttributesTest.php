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

class TalAttributesTest extends \Tests\Testcase\PhpTal
{

    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testWithContent()
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.02.html');
        $tpl->spanClass = 'dummy';
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.02.html');
        $this->assertEquals($exp, $res);
    }

    public function testMultiples()
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.03.html');
        $tpl->spanClass = 'dummy';
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.03.html');
        $this->assertEquals($exp, $res);
    }

    public function testChain()
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.04.html');
        $tpl->spanClass = 'dummy';
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.04.html');
        $this->assertEquals($exp, $res);
    }

    public function testMultipleChains()
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.05.html');
        $tpl->spanClass = 'dummy';
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.05.html');
        $this->assertEquals($exp, $res);
    }

    public function testEncoding()
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.06.html');
        $tpl->href = "http://www.test.com/?foo=bar&buz=biz&<thisissomething";
        $tpl->title = 'bla bla <blabla>';
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.06.html');
        $this->assertEquals($exp, $res);
    }

    public function testZeroValues()
    {
        $tpl = $this->newPHPTAL('input/tal-attributes.07.html');
        $tpl->href1 = 0;
        $tpl->href2 = 0;
        $tpl->href3 = 0;
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.07.html');
        $this->assertEquals($exp, $res);
    }

    public function testEmpty()
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
        $this->assertEquals($exp, $res);
    }

    public function testSingleQuote()
    {
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.08.html');
        $tpl = $this->newPHPTAL('input/tal-attributes.08.html');
        $res = $tpl->execute();
        $this->assertEquals($exp, $res);
    }

    public function testStructure()
    {
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.09.html');
        $tpl = $this->newPHPTAL('input/tal-attributes.09.html');
        $tpl->value = "return confirm('hel<lo');";
        $res = $tpl->execute();
        $this->assertEquals($exp, $res);
    }

    public function testChainedStructure()
    {
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-attributes.10.html');
        $tpl = $this->newPHPTAL('input/tal-attributes.10.html');
        $tpl->value1 = false;
        $tpl->value2 = "return confirm('hel<lo');";
        $res = $tpl->execute();
        $this->assertEquals($exp, $res);
    }

    public function testNothingValue()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:attributes="title missing | nothing"></p>');
        $res = $tpl->execute();
        $this->assertSame($res, '<p></p>');
    }

    public function testNULLValueNoAlternative()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:attributes="title NULL"></p>');
        $res = $tpl->execute();
        $this->assertSame('<p></p>', $res);
    }

    public function testEmptyValue()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:attributes="title missing | \'\'"></p><p tal:attributes="title missing | php:\'\'"></p>');
        $res = $tpl->execute();
        $this->assertEquals('<p title=""></p><p title=""></p>', $res);
    }

    public function testSemicolon()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<div><p tal:content="\'\\\'a;b;;c;;;d\'" tal:attributes="style \'color:red;; font-weight:bold;;;;\'; title php:\'\\\'test;;test;;;;test\'"></p></div>');
        $res = $tpl->execute();
        $this->assertEquals($res, '<div><p style="color:red; font-weight:bold;;" title="&#039;test;test;;test">&#039;a;b;;c;;;d</p></div>');
    }

    public function testBoolean()
    {
        $booleanAttrs = array(
            'checked','disabled','autoplay','async','autofocus','controls',
            'default','defer','formnovalidate','hidden','ismap','itemscope',
            'loop','multiple','novalidate','open','pubdate','readonly',
            'required','reversed','scoped','seamless','selected'
        );
        foreach($booleanAttrs as $name) {
            // XHTML
            $tpl = $this->newPHPTAL();
            $tpl->setOutputMode(\PhpTal\PHPTAL::XHTML);
            $tpl->setSource('<p '.$name.'="123" tal:attributes="'.$name.' attrval"></p>');
            $tpl->attrval = true;
            $res = $tpl->execute();
            $this->assertEquals('<p '.$name.'="'.$name.'"></p>', $res);
            $tpl->attrval = false;
            $res = $tpl->execute();
            $this->assertEquals('<p></p>', $res);
            // HTML5
            $tpl = $this->newPHPTAL();
            $tpl->setOutputMode(\PhpTal\PHPTAL::HTML5);
            $tpl->setSource('<p '.$name.'="123" tal:attributes="'.$name.' attrval"></p>');
            $tpl->attrval = true;
            $res = $tpl->execute();
            $this->assertEquals('<p '.$name.'></p>', $res);
            $tpl->attrval = false;
            $res = $tpl->execute();
            $this->assertEquals('<p></p>', $res);
        }
    }
}
