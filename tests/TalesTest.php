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

use PhpTal\Exception\ParserException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\Php\TalesInternal;

class TalesTest extends \Tests\Testcase\PhpTal
{

    public function tearDown(): void
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    public function testString()
    {
        $src = 'string:foo bar baz';
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals("'foo bar baz'", $res);

        $src = "'foo bar baz'";
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals("'foo bar baz'", $res);
    }

    public function testPath()
    {
        $src = 'foo/x/y';
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals("\$ctx->path(\$ctx->foo, 'x/y')", $res);
    }

    public function testNotVar()
    {
        $src = "not:foo";
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals('!\PhpTal\Helper::phptal_true($ctx->foo)', $res);
    }

    public function testChainedExists()
    {
        $tpl = $this->newPHPTAL()->setSource('<div tal:condition="exists:a | nothing">ok</div>');
        $tpl->a = array(1);
        $this->assertEquals('<div>ok</div>',$tpl->execute());
    }

    public function testNotPath()
    {
        $src = "not:foo/bar/baz";
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals('!\PhpTal\Helper::phptal_true($ctx->path($ctx->foo, \'bar/baz\'))', $res);
    }

    public function testTrue()
    {
        $tpl = $this->newPHPTAL('input/tales-true.html');
        $tpl->isNotTrue = false;
        $tpl->isTrue = true;
        $res = $tpl->execute();
        $this->assertEquals(\Tests\Testhelper\Helper::normalizeHtmlFile('output/tales-true.html'), \Tests\Testhelper\Helper::normalizeHtml($res));
    }

    public function testJSON()
    {
        $this->assertEquals('<p>{&quot;foo&quot;:&quot;bar&quot;}</p>', $this->newPHPTAL()->setSource('<p tal:content="json:php:array(&quot;foo&quot;=>&apos;bar&apos;)"/>')->execute());
        $this->assertEquals('<p>{"foo":"bar"}</p>', $this->newPHPTAL()->setSource('<p tal:content="structure json:php:array(&quot;foo&quot;=>&apos;bar&apos;)"/>')->execute());
    }

    public function testURLEncode()
    {
        $this->assertEquals('<p>Hello%20World</p>', $this->newPHPTAL()->setSource('<p tal:content="urlencode:string:Hello World"/>')->execute());
    }

    public function testTaleNeverReturnsArray()
    {
        $this->assertIsString(TalesInternal::compileToPHPExpression('foo | bar | baz | nothing'));
    }

    public function testTalesReturnsArray()
    {
        $this->assertIsArray(TalesInternal::compileToPHPExpressions('foo | bar | baz | nothing'));
    }

    public function testInterpolate1()
    {
        $this->assertEquals('$ctx->{$ctx->path($ctx->some, \'path\')}', TalesInternal::compileToPHPExpressions('${some/path}'));
    }

    public function testInterpolate2()
    {
        $this->assertEquals('$ctx->path($ctx->{$ctx->path($ctx->some, \'path\')}, \'meh\')', TalesInternal::compileToPHPExpressions('${some/path}/meh'));
    }

    public function testInterpolate3()
    {
        $this->assertEquals('$ctx->path($ctx->meh, $ctx->path($ctx->some, \'path\'))', TalesInternal::compileToPHPExpressions('meh/${some/path}'));
    }

    public function testInterpolate4()
    {
        $this->assertEquals('$ctx->path($ctx->{$ctx->meh}, $ctx->blah)', TalesInternal::compileToPHPExpressions('${meh}/${blah}'));
    }

    public function testSuperglobals()
    {
        $this->assertEquals('$ctx->path($ctx->{\'_GET\'}, \'a\')', TalesInternal::compileToPHPExpressions('_GET/a'));
    }

    public function testInterpolatedTALES()
    {
        $tpl = $this->newPHPTAL();
        $tpl->var = 'ba';
        $tpl->setSource('<div tal:content="string:foo${nonexistant | string:bar$var}z"/>');
        $this->assertEquals('<div>foobarbaz</div>', $tpl->execute());
    }

    public function testInterpolatedPHP2()
    {
        $tpl = $this->newPHPTAL();
        $tpl->somearray = array(1=>9, 9, 9);
        $tpl->setSource('<div tal:repeat="x somearray"><x tal:replace=\'repeat/${php:
            "x"}/key\'/></div>');
        $this->assertEquals('<div>1</div><div>2</div><div>3</div>', $tpl->execute());
    }

    public function testStringWithLongVarName()
    {
        $tpl = $this->newPHPTAL();
        $tpl->aaaaaaaaaaaaaaaaaaaaa = 'ok';
        $tpl->bbb = 'ok';

        $tpl->setSource('<x tal:attributes="y string:$bbb/y/y; x string:$aaaaaaaaaaaaaaaaaaaaa/x/x" />');
        $tpl->execute();
    }

    public function testThrowsInvalidPath()
    {
        $this->expectException(ParserException::class);
        TalesInternal::compileToPHPExpressions("I am not valid expression");
    }

    public function testThrowsUnknownModifier()
    {
        try {
            TalesInternal::compileToPHPExpressions('testidontexist:foo');
        } catch (UnknownModifierException $e) {
            $this->assertEquals(null, $e->getModifierName());
        }
    }

    public function testNamespaceFunction()
    {
        // todo: namespacing is gone for now, this maybe should be implemented again somehow
        TalesInternal::setFunctionWhitelist(['strlen', 'x']);
        TalesInternal::setPhpModifierAllowed(true);
        $this->assertSame('\strlen($ctx->x)', TalesInternal::compileToPHPExpressions('php:\strlen(x)'));
//        $this->assertSame('my\len($ctx->x)', TalesInternal::compileToPHPExpressions('php:my\len(x)'));
//        $this->assertSame('my\subns\len($ctx->x)', TalesInternal::compileToPHPExpressions('php:my\subns\len(x)'));
    }

    public function testNamespaceClass()
    {
        static::markTestSkipped('namespacing is gone for now, this maybe should be implemented again somehow');
        $this->assertSame('\Foo::strlen($ctx->x)', TalesInternal::compileToPHPExpressions('php:\Foo::strlen(x)'));
        $this->assertSame('My\Foo::strlen($ctx->x)', TalesInternal::compileToPHPExpressions('php:My\Foo::strlen(x)'));
    }

    public function testNamespaceConstant()
    {
        static::markTestSkipped('namespacing is gone for now, this maybe should be implemented again somehow');
        $this->assertSame('My\Foo::TAU', TalesInternal::compileToPHPExpressions('php:My\Foo::TAU'));
        $this->assertSame('$ctx->date_filter->isFilterApplied(\My\Foo::TODAY)', TalesInternal::compileToPHPExpressions("php: date_filter.isFilterApplied(\My\Foo::TODAY)"));
    }
}
