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

use Testhelper\DummyToStringObject;

class TalContentTest extends \Tests\Testcase\PhpTal
{
    public function testSimple()
    {
        $tpl = $this->newPHPTAL('input/tal-content.01.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-content.01.html');
        $this->assertEquals($exp, $res);
    }

    public function testVar()
    {
        $tpl = $this->newPHPTAL('input/tal-content.02.html');
        $tpl->content = 'my content';
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-content.02.html');
        $this->assertEquals($exp, $res);
    }

    public function testStructure()
    {
        $tpl = $this->newPHPTAL('input/tal-content.03.html');
        $tpl->content = '<foo><bar/></foo>';
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-content.03.html');
        $this->assertEquals($exp, $res);
    }

    public function testNothing()
    {
        $tpl = $this->newPHPTAL('input/tal-content.04.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-content.04.html');
        $this->assertEquals($exp, $res);
    }

    public function testDefault()
    {
        $tpl = $this->newPHPTAL('input/tal-content.05.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-content.05.html');
        $this->assertEquals($exp, $res);
    }

    public function testChain()
    {
        $tpl = $this->newPHPTAL('input/tal-content.06.html');
        $res = \Tests\Testhelper\Helper::normalizeHtml($tpl->execute());
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tal-content.06.html');
        $this->assertEquals($exp, $res);
    }

    public function testEmpty()
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
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml($exp), \Tests\Testhelper\Helper::normalizeHtml($res));
    }

    public function testObjectEcho()
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
        $this->assertEquals($res, $exp);
    }

    public function testObjectEchoStructure()
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
        $this->assertEquals($res, $exp);
    }

    /**
     * @expectedException \PhpTal\Exception\VariableNotFoundException
     */
    public function testErrorsThrow()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="erroridontexist"/>');
        $tpl->execute();
    }

    /**
     * @expectedException \PhpTal\Exception\VariableNotFoundException
     */
    public function testErrorsThrow2()
    {
        $this->markTestSkipped("tal:define and tal:attributes rely on chains not throwing");//FIXME

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="erroridontexist2 | erroridontexist2"/>');
        $tpl->execute();
    }

    /**
     * @expectedException \PhpTal\Exception\VariableNotFoundException
     */
    public function testErrorsThrow3()
    {
        $this->markTestSkipped("tal:define and tal:attributes rely on chains not throwing");//FIXME

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:replace="erroridontexist3 | erroridontexist3"/>');
        $tpl->execute();
    }

    /**
     * @expectedException \PhpTal\Exception\VariableNotFoundException
     */
    public function testErrorsThrow4()
    {
        $this->markTestSkipped("tal:define and tal:attributes rely on chains not throwing");//FIXME

        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:condition="erroridontexist4 | erroridontexist4"/>');
        $tpl->execute();
    }

    public function testErrorsSilenced()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p tal:content="erroridontexist | nothing"/>');
        $this->assertEquals('<p></p>', $tpl->execute());
    }

    public function testZeroIsNotEmpty()
    {
        $tpl = $this->newPHPTAL();
        $tpl->zero = '0';
        $tpl->setSource('<p tal:content="zero | erroridontexist"/>');
        $this->assertEquals('<p>0</p>', $tpl->execute());
    }

    public function testFalseLast()
    {
        $tpl = $this->newPHPTAL();
        $tpl->one_row = array('RESPONSIBLE_OFFICE' => 'responsible_office1');
        $tpl->setSource('<span tal:define="resp_office offices/${one_row/RESPONSIBLE_OFFICE} | false">${resp_office}</span>');

        $this->assertEquals('<span>0</span>', $tpl->execute());
    }
}
