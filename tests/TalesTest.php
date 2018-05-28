<?php

use PhpTal\Exception\UnknownModifierException;
use PhpTal\Php\TalesInternal;

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

class TalesTest extends \PHPTAL_TestCase
{
    public function testString()
    {
        $src = 'string:foo bar baz';
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals("'foo bar baz'", $res);

        $src = "'foo bar baz'";
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals("'foo bar baz'", $res);
    }

    public function testPhp()
    {
        $src = 'php: foo.x[10].doBar()';
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals('$ctx->foo->x[10]->doBar()', $res);
    }

    public function testPath()
    {
        $src = 'foo/x/y';
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals("\$ctx->path(\$ctx->foo, 'x/y')", $res);
    }

    public function testNot()
    {
        $src = "not: php: foo()";
        $res = TalesInternal::compileToPHPExpressions($src);
        $this->assertEquals("!\PhpTal\Helper::phptal_true(foo())", $res);
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
        $this->assertEquals(normalize_html_file('output/tales-true.html'), normalize_html($res));
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
        $this->assertInternalType('string', TalesInternal::compileToPHPExpression('foo | bar | baz | nothing'));
    }

    public function testTalesReturnsArray()
    {
        $this->assertInternalType('array', TalesInternal::compileToPHPExpressions('foo | bar | baz | nothing'));
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

    public function testInterpolatedPHP1()
    {
        $tpl = $this->newPHPTAL();
        $tpl->setSource('<div tal:content="string:foo${php:true?&apos;bar&apos;:0}${php:false?0:\'b$$a$z\'}"/>');
        $this->assertEquals('<div>foobarb$$a$z</div>', $tpl->execute());
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
        $tpl->setSource('<div tal:repeat="x php:somearray"><x tal:replace=\'repeat/${php:
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

    /**
     * @expectedException \PhpTal\Exception\ParserException
     */
    public function testThrowsInvalidPath()
    {
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
        $this->assertEquals('\strlen($ctx->x)', TalesInternal::compileToPHPExpressions('php:\strlen(x)'));
        $this->assertEquals('my\len($ctx->x)', TalesInternal::compileToPHPExpressions('php:my\len(x)'));
        $this->assertEquals('my\subns\len($ctx->x)', TalesInternal::compileToPHPExpressions('php:my\subns\len(x)'));
    }

    public function testNamespaceClass()
    {
        $this->assertEquals('\Foo::strlen($ctx->x)', TalesInternal::compileToPHPExpressions('php:\Foo::strlen(x)'));
        $this->assertEquals('My\Foo::strlen($ctx->x)', TalesInternal::compileToPHPExpressions('php:My\Foo::strlen(x)'));
    }

    public function testNamespaceConstant()
    {
        $this->assertEquals('My\Foo::TAU', TalesInternal::compileToPHPExpressions('php:My\Foo::TAU'));
        $this->assertEquals('$ctx->date_filter->isFilterApplied(\My\Foo::TODAY)', TalesInternal::compileToPHPExpressions("php: date_filter.isFilterApplied(\My\Foo::TODAY)"));
    }
}
