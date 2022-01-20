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

use PhpTal\Exception\ParserException;
use PhpTal\Exception\UnknownModifierException;
use PhpTal\Php\TalesInternal;
use Tests\Testcase\PhpTalTestCase;
use Tests\Testhelper\Helper;

class TalesTest extends PhpTalTestCase
{

    public function tearDown(): void
    {
        TalesInternal::setFunctionWhitelist([]);
        parent::tearDown();
    }

    public function testString(): void
    {
        $src = 'string:foo bar baz';
        $res = TalesInternal::compileToPHPExpressions($src);
        static::assertSame("'foo bar baz'", $res);

        $src = "'foo bar baz'";
        $res = TalesInternal::compileToPHPExpressions($src);
        static::assertSame("'foo bar baz'", $res);
    }

    public function testPath(): void
    {
        $src = 'foo/x/y';
        $res = TalesInternal::compileToPHPExpressions($src);
        static::assertSame("\$ctx->path(\$ctx->foo, 'x/y')", $res);
    }

    public function testNotVar(): void
    {
        $src = "not:foo";
        $res = TalesInternal::compileToPHPExpressions($src);
        static::assertSame('!\PhpTal\Helper::phptal_true($ctx->foo)', $res);
    }

    public function testChainedExists(): void
    {
        $tpl = $this->newPHPTAL()->setSource('<div tal:condition="exists:a | nothing">ok</div>');
        $tpl->a = [1];
        static::assertSame('<div>ok</div>', $tpl->execute());
    }

    public function testNotPath(): void
    {
        $src = "not:foo/bar/baz";
        $res = TalesInternal::compileToPHPExpressions($src);
        static::assertSame('!\PhpTal\Helper::phptal_true($ctx->path($ctx->foo, \'bar/baz\'))', $res);
    }

    public function testTrue(): void
    {
        $tpl = $this->newPHPTAL('input/tales-true.html');
        $tpl->isNotTrue = false;
        $tpl->isTrue = true;
        $res = $tpl->execute();
        static::assertSame(Helper::normalizeHtmlFile('output/tales-true.html'), Helper::normalizeHtml($res));
    }

    public function testJSON(): void
    {
        static::assertSame(
            '<p>{&quot;foo&quot;:&quot;bar&quot;}</p>',
            $this
                ->newPHPTAL()
                ->setSource('<p tal:content="json:php:array(&quot;foo&quot;=>&apos;bar&apos;)"/>')
                ->execute()
        );
        static::assertSame(
            '<p>{"foo":"bar"}</p>',
            $this
                ->newPHPTAL()
                ->setSource('<p tal:content="structure json:php:array(&quot;foo&quot;=>&apos;bar&apos;)"/>')
                ->execute()
        );
    }

    public function testURLEncode(): void
    {
        static::assertSame('<p>Hello%20World</p>',
            $this->newPHPTAL()->setSource('<p tal:content="urlencode:string:Hello World"/>')->execute());
    }

    public function testTaleNeverReturnsArray(): void
    {
        $this->assertIsString(TalesInternal::compileToPHPExpression('foo | bar | baz | nothing'));
    }

    public function testTalesReturnsArray(): void
    {
        static::assertIsArray(TalesInternal::compileToPHPExpressions('foo | bar | baz | nothing'));
    }

    public function testInterpolate1(): void
    {
        static::assertSame(
            '$ctx->{$ctx->path($ctx->some, \'path\')}',
            TalesInternal::compileToPHPExpressions('${some/path}')
        );
    }

    public function testInterpolate2(): void
    {
        static::assertSame(
            '$ctx->path($ctx->{$ctx->path($ctx->some, \'path\')}, \'meh\')',
            TalesInternal::compileToPHPExpressions('${some/path}/meh')
        );
    }

    public function testInterpolate3(): void
    {
        static::assertSame(
            '$ctx->path($ctx->meh, $ctx->path($ctx->some, \'path\'))',
            TalesInternal::compileToPHPExpressions('meh/${some/path}')
        );
    }

    public function testInterpolate4(): void
    {
        static::assertSame(
            '$ctx->path($ctx->{$ctx->meh}, $ctx->blah)',
            TalesInternal::compileToPHPExpressions('${meh}/${blah}')
        );
    }

    public function testSuperglobals(): void
    {
        static::assertSame('$ctx->path($ctx->{\'_GET\'}, \'a\')', TalesInternal::compileToPHPExpressions('_GET/a'));
    }

    public function testInterpolatedTALES(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->var = 'ba';
        $tpl->setSource('<div tal:content="string:foo${nonexistant | string:bar$var}z"/>');
        static::assertSame('<div>foobarbaz</div>', $tpl->execute());
    }

    public function testInterpolatedPHP2(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->somearray = [1 => 9, 9, 9];
        $tpl->setSource('<div tal:repeat="x somearray"><x tal:replace=\'repeat/${php:
            "x"}/key\'/></div>');
        static::assertSame('<div>1</div><div>2</div><div>3</div>', $tpl->execute());
    }

    public function testStringWithLongVarName(): void
    {
        $tpl = $this->newPHPTAL();
        $tpl->aaaaaaaaaaaaaaaaaaaaa = 'ok';
        $tpl->bbb = 'ok';

        $tpl->setSource('<x tal:attributes="y string:$bbb/y/y; x string:$aaaaaaaaaaaaaaaaaaaaa/x/x" />');
        $tpl->execute();
    }

    public function testThrowsInvalidPath(): void
    {
        $this->expectException(ParserException::class);
        TalesInternal::compileToPHPExpressions("I am not valid expression");
    }

    public function testThrowsUnknownModifier(): void
    {
        try {
            TalesInternal::compileToPHPExpressions('testidontexist:foo');
        } catch (UnknownModifierException $e) {
            static::assertNull($e->getModifierName());
        }
    }

    public function testNamespaceFunction(): void
    {
        TalesInternal::setFunctionWhitelist(['strlen', 'x']);
        TalesInternal::setPhpModifierAllowed(true);
        static::assertSame('\strlen($ctx->x)', TalesInternal::compileToPHPExpressions('php:\strlen(x)'));
        static::assertSame('my\len($ctx->x)', TalesInternal::compileToPHPExpressions('php:my\len(x)'));
        static::assertSame('my\subns\len($ctx->x)', TalesInternal::compileToPHPExpressions('php:my\subns\len(x)'));
    }

    public function testNamespaceClass(): void
    {
        static::assertSame('\Foo::strlen($ctx->x)', TalesInternal::compileToPHPExpressions('php:\Foo::strlen(x)'));
        static::assertSame('My\Foo::strlen($ctx->x)', TalesInternal::compileToPHPExpressions('php:My\Foo::strlen(x)'));
    }

    public function testNamespaceConstant(): void
    {
        static::assertSame('My\Foo::TAU', TalesInternal::compileToPHPExpressions('php:My\Foo::TAU'));
        static::assertSame(
            '$ctx->date_filter->isFilterApplied(\My\Foo::TODAY)',
            TalesInternal::compileToPHPExpressions("php: date_filter.isFilterApplied(\My\Foo::TODAY)")
        );
    }
}
