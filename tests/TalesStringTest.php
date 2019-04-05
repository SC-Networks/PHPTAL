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

class TalesStringTest extends \Tests\Testcase\PhpTal {

    public function testSimple()
    {
        $this->assertEquals('\'this is a string\'', \PhpTal\Php\TalesInternal::string('this is a string'));
    }

    public function testDoubleDollar()
    {
        $this->assertEquals('\'this is a $string\'', \PhpTal\Php\TalesInternal::string('this is a $$string'));
    }

    public function testSubPathSimple()
    {
        $res = \PhpTal\Php\TalesInternal::string('hello $name how are you ?');
        $this->assertRegExp('/\'hello \'.*?\$ctx->name.*?\' how are you \?\'$/', $res);
    }

    public function testSubPath()
    {
        $res = \PhpTal\Php\TalesInternal::string('${name}');
        $this->assertRegExp('/^(\'\'\s*?\.*)?\$ctx->name(.*?\'\')?$/', $res);
    }

    public function testSubPathExtended()
    {
        $res = \PhpTal\Php\TalesInternal::string('hello ${user/name} how are you ?');
        $this->assertRegExp('/\'hello \'.*?\$ctx->user, \'name\'.*?\' how are you \?\'$/', $res);
    }

    public function testQuote()
    {
        $tpl = $this->newPHPTAL('input/tales-string-01.html');
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tales-string-01.html');
        $this->assertEquals($exp, $res);
    }

    public function testDoubleVar()
    {
        $res = \PhpTal\Php\TalesInternal::string('hello $foo $bar');
        $this->assertRegExp('/ctx->foo/', $res, '$foo not interpolated');
        $this->assertRegExp('/ctx->bar/', $res, '$bar not interpolated');
    }

    public function testDoubleDotComa()
    {
        $tpl = $this->newPHPTAL('input/tales-string-02.html');
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tales-string-02.html');
        $this->assertEquals($exp, $res);
    }

    public function testEscape()
    {
        $tpl = $this->newPHPTAL('input/tales-string-03.html');
        $res = $tpl->execute();
        $res = \Tests\Testhelper\Helper::normalizeHtml($res);
        $exp = \Tests\Testhelper\Helper::normalizeHtmlFile('output/tales-string-03.html');
        $this->assertEquals($exp, $res);
    }

    public function testStructure()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>
            ${string:&lt;foo/&gt;}
            ${structure string:&lt;foo/&gt;}
            <x y="${string:&lt;foo/&gt;}" tal:content="string:&lt;foo/&gt;" />
            <x y="${structure string:&lt;foo/&gt;}" tal:content="structure string:&lt;foo/&gt;" />
        </p>');
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtml('<p>&lt;foo/&gt;<foo/><x y="&lt;foo/&gt;">&lt;foo/&gt;</x><x y="<foo/>"><foo/></x></p>'),
                            \Tests\Testhelper\Helper::normalizeHtml($tpl->execute()));
    }
}
