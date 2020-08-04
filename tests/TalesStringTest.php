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

use PhpTal\Php\TalesInternal;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class TalesStringTest extends PhpTalTestCase
{

    public function testSimple(): void
    {
        static::assertSame('\'this is a string\'', TalesInternal::string('this is a string'));
    }

    public function testDoubleDollar(): void
    {
        static::assertSame('\'this is a $string\'', TalesInternal::string('this is a $$string'));
    }

    public function testSubPathSimple(): void
    {
        $res = TalesInternal::string('hello $name how are you ?');
        static::assertMatchesRegularExpression('/\'hello \'.*?\$ctx->name.*?\' how are you \?\'$/', $res);
    }

    public function testSubPath(): void
    {
        $res = TalesInternal::string('${name}');
        static::assertMatchesRegularExpression('/^(\'\'\s*?\.*)?\$ctx->name(.*?\'\')?$/', $res);
    }

    public function testSubPathExtended(): void
    {
        $res = TalesInternal::string('hello ${user/name} how are you ?');
        static::assertMatchesRegularExpression('/\'hello \'.*?\$ctx->user, \'name\'.*?\' how are you \?\'$/', $res);
    }

    public function testQuote(): void
    {
        $tpl = $this->newPHPTAL('input/tales-string-01.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tales-string-01.html');
        static::assertSame($exp, $res);
    }

    public function testDoubleVar(): void
    {
        $res = TalesInternal::string('hello $foo $bar');
        static::assertMatchesRegularExpression('/ctx->foo/', $res, '$foo not interpolated');
        static::assertMatchesRegularExpression('/ctx->bar/', $res, '$bar not interpolated');
    }

    public function testDoubleDotComa(): void
    {
        $tpl = $this->newPHPTAL('input/tales-string-02.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tales-string-02.html');
        static::assertSame($exp, $res);
    }

    public function testEscape(): void
    {
        $tpl = $this->newPHPTAL('input/tales-string-03.html');
        $res = $tpl->execute();
        $res = Helper::normalizeHtml($res);
        $exp = Helper::normalizeHtmlFile('output/tales-string-03.html');
        static::assertSame($exp, $res);
    }

    public function testStructure(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<p>
            ${string:&lt;foo/&gt;}
            ${structure string:&lt;foo/&gt;}
            <x y="${string:&lt;foo/&gt;}" tal:content="string:&lt;foo/&gt;" />
            <x y="${structure string:&lt;foo/&gt;}" tal:content="structure string:&lt;foo/&gt;" />
        </p>');
        static::assertSame(
            Helper::normalizeHtml('<p>&lt;foo/&gt;<foo/><x y="&lt;foo/&gt;">&lt;foo/&gt;</x><x y="<foo/>"><foo/></x></p>'),
            Helper::normalizeHtml($tpl->execute())
        );
    }
}
